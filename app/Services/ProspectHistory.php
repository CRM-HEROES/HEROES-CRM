<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Prospect;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection as SupportCollection;

class ProspectHistory
{
    private const TYPES = [
        'events' => 'event',
        'files' => 'file',
        'groups' => 'group',
        'interactions' => 'interaction',
        'labels' => 'label',
        'links' => 'link',
        'messages' => 'message',
        'orders' => 'order',
        'revisionHistory' => 'revision',
        'sms' => 'sms',
        'users' => 'user',
    ];

    /**
     * Load the timeline for the current page without changing its table relations.
     */
    public function append(Collection $prospects, Project $project): void
    {
        if ($prospects->isEmpty()) {
            return;
        }

        [$revisionFields, $relations] = $this->configuration($project);
        $histories = $this->hydrate($prospects, $project, $revisionFields, $relations);

        foreach ($prospects as $prospect) {
            $prospect->setAttribute('history', $histories->get($prospect->id)->all());
        }
    }

    /**
     * Count in SQL and hydrate only the latest visible entry for each prospect.
     */
    public function appendSummary(Collection $prospects, Project $project): void
    {
        if ($prospects->isEmpty()) {
            return;
        }

        [$revisionFields, $relations] = $this->configuration($project);
        $prototype = $prospects->first();
        $union = null;

        foreach (array_keys(self::TYPES) as $rank => $name) {
            $relation = Relation::noConstraints(fn () => $prototype->{$name}());
            $relations[$name]($relation);
            [$prospectKey, $itemKey, $createdAt] = $this->columns($relation);

            // noConstraints also removes the morph discriminator.
            if ($relation instanceof MorphMany) {
                $relation->where($relation->getQualifiedMorphType(), $relation->getMorphClass());
            }

            $query = $relation->getQuery()
                ->whereIn($prospectKey, $prospects->modelKeys())
                ->whereNotNull($createdAt)
                ->select([$prospectKey . ' as prospect_id', $itemKey . ' as item_id', $createdAt . ' as created_at'])
                ->selectRaw('? as type_rank', [$rank])
                ->toBase();
            $union = $union ? $union->unionAll($query) : $query;
        }

        $connection = $prototype->getConnection();
        $ranked = $connection->query()->fromSub($union, 'history_entries')
            ->select('prospect_id', 'item_id', 'created_at', 'type_rank')
            ->selectRaw('COUNT(*) OVER (PARTITION BY prospect_id) as history_count')
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY prospect_id ORDER BY created_at DESC, type_rank, item_id DESC) as history_rank');
        $latest = $connection->query()->fromSub($ranked, 'ranked_history')
            ->where('history_rank', 1)->get()->keyBy('prospect_id');

        $latestRelations = [];
        foreach (array_keys(self::TYPES) as $rank => $name) {
            $references = $latest->where('type_rank', $rank);
            if ($references->isEmpty()) {
                continue;
            }

            $relation = Relation::noConstraints(fn () => $prototype->{$name}());
            [$prospectKey, $itemKey, $createdAt] = $this->columns($relation);
            $constraint = $relations[$name];
            $latestRelations[$name] = function ($query) use ($constraint, $references, $prospectKey, $itemKey, $createdAt) {
                $constraint($query);
                $query->where(function ($query) use ($references, $prospectKey, $itemKey, $createdAt) {
                    foreach ($references as $reference) {
                        $query->orWhere(function ($query) use ($reference, $prospectKey, $itemKey, $createdAt) {
                            $query->where($prospectKey, $reference->prospect_id)
                                ->where($itemKey, $reference->item_id)
                                ->where($createdAt, $reference->created_at);
                        });
                    }
                });
            };

            foreach ($relations as $nested => $constraint) {
                if (str_starts_with($nested, $name . '.')) {
                    $latestRelations[$nested] = $constraint;
                }
            }
        }

        $histories = $this->hydrate($prospects, $project, $revisionFields, $latestRelations);
        foreach ($prospects as $prospect) {
            $prospect->setAttribute('history_latest', $histories->get($prospect->id)->first());
            $prospect->setAttribute('history_count', (int) ($latest->get($prospect->id)->history_count ?? 0));
        }
    }

    private function columns(Relation $relation): array
    {
        $model = $relation->getRelated();

        return [
            $relation instanceof BelongsToMany ? $relation->getQualifiedForeignPivotKeyName() : $relation->getQualifiedForeignKeyName(),
            $model->getQualifiedKeyName(),
            $relation instanceof BelongsToMany ? $relation->qualifyPivotColumn('created_at') : $model->qualifyColumn('created_at'),
        ];
    }

    /**
     * Share project and visibility constraints between summary and full history.
     */
    private function configuration(Project $project): array
    {
        $fields = $project->fields()->where('for', 'prospect')->get(['slug', 'meta', 'name']);
        $categories = $project->categories()->where('for', 'prospect')->get(['id', 'name']);
        $revisionFields = $fields->mapWithKeys(function ($field) {
            return [($field->meta ? 'meta->' : '') . $field->slug => $field->name];
        })->merge($categories->mapWithKeys(function ($category) {
            return ['category->' . $category->id => $category->name];
        }));

        $user = auth()->user();
        $eventCreators = $user->can('prospectEventView', $project) ? null : array_merge(
            [$user->id],
            $user->assignableUsers()->pluck('users.id')->all()
        );

        $relations = [
            'events' => function ($query) use ($project, $eventCreators) {
                $query->select('id', 'prospect_id', 'calendar_id', 'user_id', 'started_at', 'location', 'description', 'created_at')
                    ->whereHas('calendar', fn ($query) => $query->where('project_id', $project->id))
                    ->when($eventCreators !== null, fn ($query) => $query->whereIn('events.creator_id', $eventCreators));
            },
            'events.calendar' => fn ($query) => $query->select('id', 'name', 'color', 'bgcolor'),
            'files' => function ($query) use ($project, $user) {
                $query->select('id', 'prospect_id', 'folder_id', 'name', 'creator_id', 'created_at')
                    ->whereHas('folder', function ($query) use ($project) {
                        $query->where('project_id', $project->id)->where('for', 'prospect');
                    })
                    ->where(function ($query) use ($user) {
                        $query->whereHas('folder.users', fn ($query) => $query->where('users.id', $user->id))
                            ->orWhereHas('users', fn ($query) => $query->where('users.id', $user->id));
                    });
            },
            'files.folder' => fn ($query) => $query->select('id', 'name'),
            'groups' => function ($query) {
                $query->select('groups.id', 'name')->withPivot('created_at', 'creator_id');
            },
            'interactions' => fn ($query) => $query->select('id', 'prospect_id', 'source', 'creator_id', 'created_at'),
            'labels' => function ($query) use ($categories) {
                $query->select('labels.id', 'name', 'category_id', 'color', 'bgcolor')
                    ->whereIn('category_id', $categories->pluck('id'))
                    // Keep assignments from imports/bulk actions visible until
                    // their own original date has been preserved in a revision.
                    ->whereNotExists(function ($query) use ($categories) {
                        $query->selectRaw('1')->from('revisions')
                            ->whereColumn('revisions.revisionable_id', 'prospect_label.prospect_id')
                            ->whereColumn('revisions.created_at', 'prospect_label.created_at')
                            ->where('revisions.revisionable_type', (new Prospect())->getMorphClass())
                            ->where(function ($query) {
                                $query->whereColumn('revisions.user_id', 'prospect_label.creator_id')
                                    ->orWhere(function ($query) {
                                        $query->whereNull('revisions.user_id')->whereNull('prospect_label.creator_id');
                                    });
                            })
                            ->where(function ($query) use ($categories) {
                                foreach ($categories as $category) {
                                    $query->orWhere(function ($query) use ($category) {
                                        $query->where('labels.category_id', $category->id)
                                            ->where('revisions.key', 'category->' . $category->id);
                                    });
                                }
                            });
                        app(ProspectLabelHistory::class)->whereRecordedAssignment($query);
                    })
                    ->withPivot('created_at', 'creator_id');
            },
            'links' => fn ($query) => $query->select('id', 'prospect_id', 'name', 'link', 'creator_id', 'created_at'),
            'messages' => function ($query) use ($project) {
                $query->select('id', 'prospect_id', 'thread_id', 'body', 'creator_id', 'created_at')
                    ->whereHas('thread', fn ($query) => $query->where('project_id', $project->id));
            },
            'orders' => fn ($query) => $query->select('id', 'prospect_id', 'name', 'creator_id', 'created_at'),
            'orders.products' => fn ($query) => $query->select('products.id', 'products.name'),
            'revisionHistory' => function ($query) use ($revisionFields) {
                $query->select('id', 'revisionable_id', 'revisionable_type', 'key', 'old_value', 'new_value', 'user_id', 'created_at')
                    ->whereIn('key', $revisionFields->keys());
            },
            'sms' => fn ($query) => $query->select('id', 'prospect_id', 'source', 'message', 'creator_id', 'created_at'),
            'users' => function ($query) {
                $query->select('users.id', 'name')->withPivot('created_at', 'creator_id');
            },
        ];

        return [$revisionFields, $relations];
    }

    private function hydrate(Collection $prospects, Project $project, SupportCollection $revisionFields, array $relations): SupportCollection
    {
        $historyProspects = new Collection($prospects->map(function ($prospect) {
            return (clone $prospect)->unsetRelations();
        })->all());
        if ($relations) {
            $historyProspects->load($relations);
        }

        $typeRanks = array_flip(array_values(self::TYPES));
        $histories = $historyProspects->mapWithKeys(function ($prospect) use ($revisionFields, $project, $typeRanks) {
            $history = collect();

            foreach (self::TYPES as $relation => $type) {
                if (!$prospect->relationLoaded($relation)) {
                    continue;
                }
                foreach ($prospect->getRelation($relation) as $item) {
                    // Read selected attributes directly: model appends can load extra relations.
                    $entry = $item->getAttributes();
                    $entry['type'] = $type;

                    if (in_array($type, ['group', 'label', 'user'], true)) {
                        $entry['created_at'] = $item->pivot->created_at;
                        $entry['creator_id'] = $item->pivot->creator_id;
                    }

                    if (empty($entry['created_at'])) {
                        continue;
                    }

                    $entry['created_at'] = Carbon::parse($entry['created_at'])->toJSON();

                    if ($type === 'revision') {
                        $entry['field'] = $revisionFields->get($item->key);
                    } elseif ($type === 'event') {
                        $entry['calendar'] = $item->calendar ? $item->calendar->only('id', 'name', 'color', 'bgcolor') : null;
                    } elseif ($type === 'file') {
                        $entry['folder'] = $item->folder ? $item->folder->only('id', 'name') : null;
                        $entry['thumbnail'] = route('api.project.prospect.folder.file.thumbnail', [
                            'project' => $project->slug,
                            'prospect' => $prospect->id,
                            'folder' => $item->folder_id,
                            'file' => $item->id,
                        ]);
                    } elseif ($type === 'order') {
                        $entry['name'] = $item->name;
                    }

                    $history->push($entry);
                }
            }

            return [$prospect->id => $history->sort(static function (array $left, array $right) use ($typeRanks): int {
                return ($right['created_at'] <=> $left['created_at'])
                    ?: ($typeRanks[$left['type']] <=> $typeRanks[$right['type']])
                    ?: ($right['id'] <=> $left['id']);
            })->values()];
        });

        $userIds = $histories->flatten(1)->flatMap(function ($entry) {
            return [$entry['creator_id'] ?? null, $entry['user_id'] ?? null];
        })->filter()->unique();
        $users = User::query()->whereIn('id', $userIds)->get(['id', 'name'])->keyBy('id');

        return $histories->map(function ($history) use ($users) {
            return $history->map(function ($entry) use ($users) {
                $entry['creator'] = $users->get($entry['creator_id'] ?? null);
                $entry['user'] = $users->get($entry['user_id'] ?? null);

                return $entry;
            });
        });
    }
}
