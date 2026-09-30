<?php

namespace App\Http\Controllers\API\Project;

use App\Filters\EventFilters;
use App\Filters\ProspectRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\UserSetting;
use App\Utils\ProjectSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProspectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ProspectRequestFilters $filters, Project $project)
    {
        return $this->getProspects(
            $request,
            $filters,
            $project
        );
    }

    /**
     * Display a listing of the resource.
     */
    public function count(ProspectRequestFilters $filters, Project $project)
    {
        return $project
            ->prospects()
            ->filter($filters)
            ->count();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('prospectAdd', $project), 404);

        $defaultFieldValues = $request->only(
            $project->fields()
                ->where('for', "prospect")
                ->where('meta', false)
                ->get()
                ->pluck('slug')
                ->toArray()
        );

        $metaFieldValues = collect($request->meta)->only(
            $project->fields()
                ->where('for', "prospect")
                ->where('meta', true)
                ->get()
                ->pluck('slug')
                ->toArray()
        )->toArray();

        $prospect =  $project->prospects()->create(array_merge(
            $defaultFieldValues,
            [
                'meta' => $metaFieldValues,
                'creator_id' => auth()->id(),
            ]
        ));

        $prospect->load('creator');
        $prospect->load('users');

        return $prospect;
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Prospect $prospect)
    {
        /*$fields = $this->getShowFields($project);
        $prospect = Prospect::where('id', $prospect->id)->select(array_merge(['id', 'meta'], $fields['default']))->first();
        $prospect->meta = $prospect->meta ? array_filter($prospect->meta, function($key) use($fields) {
            return in_array("meta->$key", $fields['meta']);
        }, ARRAY_FILTER_USE_KEY) : null;*/

        $prospect->load([
            'events' => function($query) {
                $query->select('id', 'started_at', 'prospect_id', 'order_id', 'user_id', 'calendar_id', 'done', 'location', 'confirmed', 'created_at', 'latitude', 'longitude');
            },
            'events.calendar' => function($query) {
                $query->select('id', 'color', 'bgcolor', 'name', 'type');
            },
            'events.order' => function($query) {
                $query->select('id', 'name');
            },
            'events.user' => function($query) {
                $query->select('id', 'name');
            },
            'events.googleAccounts' => function($query) {
                // $query->select('id', 'name');
            },
            'files' => function($query) {
            },
            'files.folder' => function($query) {
                $query->select('id', 'project_id', 'name');
            },
            'files.folder.project' => function($query) {
                $query->select('id', 'slug');
            },
            'files.creator' => function($query) {
                $query->select('id', 'name');
            },
            'groups' => function($query) {
                $query->select('id', 'name', 'color', 'bgcolor', 'prospect_group.created_at', 'prospect_group.creator_id');
            },
            'interactions' => function($query) {
                $query->select('id', 'source', 'prospect_id', 'created_at', 'creator_id');
            },
            'interactions.creator' => function($query) {
                $query->select('id', 'name');
            },
            'labels' => function($query) {
                $query->select('id', 'name', 'color', 'bgcolor', 'category_id', 'prospect_label.created_at', 'prospect_label.creator_id');
            },
            'links' => function($query) {
                $query->select('id', 'name', 'link', 'prospect_id', 'created_at');
            },
            'orders' => function($query) {
            },
            'orders.actions' => function($query) {
            },
            'orders.products' => function($query) {
            },
            'orders.products.images' => function($query) {
            },
            'orders.steps' => function($query) {
            },
            'orders.status' => function($query) {
            },
            'orders.creator' => function($query) {
                $query->select('id', 'name');
            },
            'sms' => function($query) {
                $query->select('id', 'message', 'error', 'source', 'prospect_id', 'creator_id', 'created_at');
            },
            'sms.creator' => function($query) {
                $query->select('id', 'name');
            },
            'users' => function($query) {
                $query->select('id', 'name', 'prospect_user.created_at', 'prospect_user.creator_id');
            },
        ]);

        return $prospect;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Prospect $prospect)
    {
        $defaultFieldValues = $request->only(
            $project->fields()
                ->where('for', "prospect")
                ->where('meta', false)
                ->get()
                ->pluck('slug')
                ->toArray()
        );

        $metaFieldValues = collect($request->meta)->only(
            $project->fields()
                ->where('for', "prospect")
                ->where('meta', true)
                ->get()
                ->pluck('slug')
                ->toArray()
        )->toArray();

        $prospect->update(array_merge(
            $defaultFieldValues, ['meta' => array_merge($prospect->meta ?: [], $metaFieldValues)]
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Prospect $prospect)
    {
        $prospect->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }

    /**
     * Update the specified resource in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        $this->validate($request, [
            'prospects' => 'required',
            'action' => 'required',
        ]);

        // Only prospects
        // associated to the current project
        $prospects = $project
            ->prospects()
            ->whereIn('id', $request->input('prospects'));
            // ->withTrashed();

        switch ($request->input('action')) {
            case "delete":
                $prospects->delete();
                return ['message' => trans('common.success.deleted_resource')];

            case "force_delete":
                $prospects->forceDelete();
                return ['message' => trans('common.success.deleted_resource')];

            case "restore":
                $prospects->restore();
                return ['message' => trans('common.success.updated_resource')];

            case "processed":
                $prospects->update(['processed_by' => auth()->id(), 'processed_at' => \Carbon\Carbon::now()]);
                return ['message' => trans('common.success.updated_resource')];

            case "not_processed":
                $prospects->update(['processed_by' => null, 'processed_at' => null]);
                return ['message' => trans('common.success.updated_resource')];

            case "field":
                $this->validate($request, [
                    'field' => 'required',
                    'value' => 'required',
                ]);

                $value = $request->input('value');
                $meta = Str::startsWith($request->input('field'), 'meta->');
                $field = Str::replace('meta->', '', $request->input('field'));

                if (!$project->fields()->where('slug', $field)->where('meta', $meta)->first()) {
                    return response('Unknown bulk action.', 400);
                }

                if ($meta) {
                    foreach ($prospects->get() as $prospect) {
                        $meta = $prospect->meta ? $prospect->meta : [];
                        $meta[$field] = $value;
                        $prospect->update([
                            'meta' => $meta
                        ]);
                    }
                } else {
                    $prospects->update([
                        $field => $value
                    ]);
                }
                return ['message' => trans('common.success.updated_resource')];

            case "project":
                $this->validate($request, [
                    'project' => 'required',
                ]);

                abort_unless($project = Project::find($request->input('project')), 404);

                $this->copyToProject($prospects->get(), $project, $request->input('mapping', []));

                return ['message' => trans('common.success.updated_resource')];
            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     *
     */
    protected function copyToProject($prospects, Project $project, $mapping = [])
    {
        $categories = count($mapping) > 0 ? $project->categories()->with('labels')->get() : null;

        $date = new \Carbon\Carbon();
        $prospects_labels = [];

        foreach ($prospects as $prospect) {
            // Replicate prospect
            // in the other project
            $p = $prospect->replicate();
            $p->project_id = $project->id;
            $p->created_at = $date;
            $p->updated_at = $date;
            $p->save();

            // Copy prospect labels
            // from $categoryId to $mapId
            foreach ($mapping as $categoryId => $mapId) {
                // Check mapped category from the other project
                $category = $categories->where('id', $mapId)->first();
                if (!$category) {
                    continue;
                }

                // Copy and attach labels
                $labels = $prospect->labels()->where('category_id', $categoryId)->get();
                foreach ($labels as $label) {
                    // Create label in the mapped category
                    // if does not exist
                    if (!($l = $category->labels->where('name', $label['name'])->first())) {
                        $l = $label->replicate();
                        $l->category_id = $category->id;
                        $l->save();

                        $category->load('labels');
                    }

                    $prospects_labels[] = [
                        "prospect_id" => $p->id,
                        "label_id" => $l->id,
                        "created_at" => $date,
                        "updated_at" => $date,
                    ];
                }
            }
        }

        // Bulk attach prospects labels
        DB::table("prospect_label")->insert($prospects_labels);
    }

    /**
     * Duplicate the specified resource in storage.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function duplicate(Project $project, Prospect $prospect)
    {
        abort_unless(auth()->user()->can('prospectAdd', $project), 404);

        $copy = Prospect::create(
            array_merge(
                $prospect
                    ->only(
                        array_merge($project->fields()->where('meta', 0)->get(['slug'])->pluck('slug')->toArray(), ['latitude', 'longitude', 'project_id', 'created_at'])
                    ),
                [
                    'creator_id' => auth()->id(),
                    // 'created_at' => $prospect->created_at,
                    'meta' => collect($prospect->meta)
                                ->only($project->fields()->where('meta', 1)->get(['slug'])->pluck('slug'))
                                ->toArray()
                ]
            )
        );

        // Associated labels
        $copy->labels()->attach($prospect->labels()->get(['id'])->pluck('id'));

        // Associated users
        $copy->users()->attach($prospect->users()->get(['id'])->pluck('id'));

        // Associated groups
        $copy->groups()->attach($prospect->groups()->get(['id'])->pluck('id'));

        $copy->load('labels:id,name,color,bgcolor,category_id');
        $copy->load('users:id,name');
        $copy->load('groups:id,name,color,bgcolor');

        return $copy;
    }

    /**
     * Get fields
     */
    protected function getShowFields(Project $project)
    {
        // From user settings
        $userSetting = UserSetting::where([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'key' => 'prospect-profile',
        ])->first();

        if ($userSetting && $userSetting->value) {
            $userSetting = collect(
                    collect($userSetting->value)
                    ->reduce(function($carry, $column) {
                        return array_merge($carry, $column);
                    }, [])
                )->filter(function($bloc) {
                    return $bloc['type'] == 'field';
                })->reduce(function($carry, $bloc) {
                    return array_merge($carry, $bloc['items']);
                }, []);
        }

        return [
            'default' => $project
                ->fields()
                ->where('for', "prospect")
                ->where('meta', 0)
                ->when($userSetting, function($query) use($userSetting) {
                    $userSettingDefaultFields = array_filter($userSetting, function($field) {
                        return !Str::startsWith($field, 'meta->');
                    });

                    $query->whereIn('slug', $userSettingDefaultFields);
                })
                ->get()
                ->map(function($field) {
                    return ($field->meta ? 'meta->' : '') . $field->slug;
                })
                ->toArray(),

            'meta' => $project
                ->fields()
                ->where('for', "prospect")
                ->where('meta', 1)
                ->when($userSetting, function($query) use($userSetting) {
                    $userSettingMetaFields = array_map(function($field) {
                        return str_replace('meta->', '', $field);
                    }, array_filter($userSetting, function($field) {
                        return Str::startsWith($field, 'meta->');
                    }));

                    $query->whereIn('slug', $userSettingMetaFields);
                })
                ->get()
                ->map(function($field) {
                    return ($field->meta ? 'meta->' : '') . $field->slug;
                })
                ->toArray()
        ];
    }

    /**
     * Get fields
     */
    protected function getFields(Request $request, Project $project)
    {
        // From params
        if ($request->has('fields')) {
            return explode(',', $request->input('fields'));
        }

        // From user settings
        $userSetting = UserSetting::where([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'key' => 'prospects-table',
        ])->first();

        if ($userSetting) {
            return array_map(function($field) {
                return $field['key'];
            }, $userSetting->value);
        }

        // From project settings
        $projectSetting = collect(ProjectSetting::get($project, 'prospects-table'));

        if ($projectSetting) {
            return $projectSetting->map(function($field) {
                return $field['key'];
            })->toArray();
        }

        // From project settings
        $defaultSetting = config('default-settings.prospects-table', null);

        if ($defaultSetting) {
            return collect($defaultSetting)->map(function($field) {
                return $field['key'];
            })->toArray();
        }

        // Default
        return array_merge(
            $project
                ->fields()
                ->where('for', "prospect")
                ->where('meta', false)
                ->get()
                ->pluck('slug')
                ->toArray(), [
                    'events', 'users'
                ]
        );
    }

    /**
     * Get prospects
     */
    protected function getProspects(Request $request, ProspectRequestFilters $filters, Project $project)
    {
        // Count
        $count = min($request->input('count', 50), 500);

        // Sort By
        $sortBy = $request->input('sortBy', "id");

        if ($sortBy == "null") {
            $sortBy = null;
        } else if (
            $sortBy != 'events_started_at' &&
            $sortBy != 'interactions_created_at' &&
            $sortBy != 'sms_created_at' &&
            $sortBy != 'messages_created_at' &&
            !$project
                ->fields()
                ->where('slug', Str::replace('meta->', '', $sortBy))
                ->where('meta', Str::startsWith($sortBy, 'meta->'))
                ->first()
        ) {
            $sortBy = "created_at";
        }

        // Sort Order
        $sortOrder = $request->input('sortOrder', "desc") == "desc" ? "desc" : "asc";

        // Fields
        $fields = $this->getFields(
            $request,
            $project
        );

        // Fields to select
        $defaultFields = array_merge(
            $project
                ->fields()
                ->whereIn('slug', $fields)
                ->where('meta', false)
                ->where('for', 'prospect')
                ->get()
                ->pluck('slug')
                ->toArray(), [
                    'id',
                    'latitude',
                    'longitude',
                    'meta',
                    'prospects.creator_id',
                ],
                $request->has('fields') ? [] : [
                    'valid_address',
                    'mobile_phone_number',
                    'phone_number',
                    'processed_at',
                    'prospects.deleted_at'
                ]
        );

        if (in_array('import', $fields)) {
            $defaultFields[] = "import_id";
        }

        // Categories in which we select labels associated to prospects
        $categories = array_map(function($field) {
            return Str::replace('category->', '', $field);
        }, array_filter($fields, function($field) {
            return Str::startsWith($field, 'category->');
        }));

        // Pipelines in which we select labels associated to prospects
        $pipelines = array_map(function($field) {
            return Str::replace('pipeline->', '', $field);
        }, array_filter($fields, function($field) {
            return Str::startsWith($field, 'pipeline->');
        }));

        // Threads in which we select messages associated to prospects
        $threads = array_map(function($field) {
            return Str::replace('thread->', '', $field);
        }, array_filter($fields, function($field) {
            return Str::startsWith($field, 'thread->');
        }));


        // Result
        $data = $project
            ->prospects()
            ->select($defaultFields)

            // Labels
            ->when(!empty($categories), function($query) use($categories, $pipelines) {
                $query->with(['labels' => function($query) use($categories) {
                    $query->whereIn('category_id', $categories)->select('id', 'name', 'category_id', 'color', 'bgcolor');
                }]);

            // Labels from pipelines
            }, function($query) use($pipelines) {
                $query->when(!empty($pipelines), function($query) use($pipelines) {
                    $query->with(['labels' => function($query) use($pipelines) {
                        $labels = DB::table('pipeline_label')->whereIn('pipeline_id', $pipelines)->get(['label_id'])->pluck('label_id')->toArray();
                        $query->whereIn('id', $labels)->select('id', 'name', 'category_id', 'color', 'bgcolor');
                    }]);
                });
            })

            // Messages
            ->when(!empty($threads), function($query) use($threads) {
                $query->with([
                    'messages' => function($query) use($threads) {
                        $query->whereIn('thread_id', $threads)->select('id', 'body', 'prospect_id', 'thread_id', 'created_at', 'creator_id');
                    },
                    'messages.creator' => function($query) {
                        $query->select('id', 'name');
                    },
                    'messages.users' => function($query) {
                        $query->where('id', auth()->id())->select('id', 'name');
                    }
                ]);
            }, function($query) {
                $query->with([
                    'messages' => function($query) {
                        $query->select('id', 'prospect_id');
                    },
                ]);
            })

            ->with([
                'files' => function($query) {
                    $query->select('id', 'prospect_id');
                },
            ])

            // When we order by message created at date
            ->when($sortBy == 'messages_created_at', function($query) {
                $query->withAggregate('messages', 'created_at');
            })

            // Interactions
            ->when(in_array('interactions', $fields), function($query) {
                $query->with([
                    'interactions' => function($query) {
                        $query->select('id', 'source', 'prospect_id', 'created_at', 'creator_id');
                    },
                    'interactions.creator' => function($query) {
                        $query->select('id', 'name');
                    }
                ]);
            }, function($query) {
                $query->with([
                    'interactions' => function($query) {
                        $query->select('id', 'prospect_id');
                    },
                ]);
            })
            // When we order by interaction created at date
            ->when($sortBy == 'interactions_created_at', function($query) {
                $query->withAggregate('interactions', 'created_at');
            })

            // Sms
            ->when(in_array('sms', $fields), function($query) {
                $query->with([
                    'sms' => function($query) {
                        $query->select('id', 'source', 'prospect_id', 'message', 'error', 'created_at', 'creator_id');
                    },
                    'sms.creator' => function($query) {
                        $query->select('id', 'name');
                    }
                ]);
            }, function($query) {
                $query->with([
                    'sms' => function($query) {
                        $query->select('id', 'prospect_id');
                    }
                ]);
            })
            // When we order by interaction created at date
            ->when($sortBy == 'sms_created_at', function($query) {
                $query->withAggregate('sms', 'created_at');
            })

            // Events
            ->when(in_array('events', $fields) || count(array_filter($fields, function($field) {
                return Str::startsWith($field, 'event->') || Str::startsWith($field, 'calendar->');
            })) > 0, function($query) use($filters) {
                $query->with([
                    'events' => function($query) use($filters) {
                        $query
                            // ->select('id', 'started_at', 'ended_at', 'prospect_id', 'calendar_id', 'done', 'confirmed')
                            // Filter events list
                            ->when($filters->getFilter('withEvents'), function($query) use($filters) {
                                $eventFilters = new EventFilters();
                                foreach ($filters->getFilter('withEvents') as $key => $value) {
                                    $eventFilters->addFilter($key, $value);
                                }
                                $query->filter($eventFilters);
                            });
                    },
                    'events.calendar' => function($query) {
                        $query->select('id', 'color', 'bgcolor', 'name', 'type');
                    },
                    'events.user' => function($query) {
                        $query->select('id', 'name');
                    },
                    'events.vehicle' => function($query) {
                        $query->select('id', 'name');
                    },
                    'events.order' => function($query) {
                        $query->select('id', 'name');
                    },
                ]);
            }, function($query) use($filters) {
                $query->with([
                    'events' => function($query) use($filters) {
                        $query->select('id', 'prospect_id');
                    }
                ]);
            })
            // When we order by event started at date
            ->when($sortBy == 'events_started_at', function($query) {
                $query->withAggregate('events', 'started_at');
            })
            // When we order by event started at date
            ->when($sortBy == 'events_started_at', function($query) {
                $query->withAggregate('events', 'started_at');
            })

            // Users
            ->when(in_array('users', $fields), function($query) {
                $query->with('users:id,name');
            })

            // Groups
            ->when(in_array('groups', $fields), function($query) {
                $query->with('groups:id,name,color,bgcolor');
            })

            // Orders
            ->when(in_array('orders', $fields), function($query) {
                $query->with([
                    'orders' => function($query) {
                        $query->select('id', 'prospect_id', 'name', 'status_id');
                    },
                    'orders.status' => function($query) {
                        $query->select('id', 'color', 'bgcolor', 'name');
                    },
                ]);
            }, function($query) {
                $query->with([
                    'orders' => function($query) {
                        $query->select('id', 'prospect_id');
                    },
                ]);
            })

            // Import
            ->when(in_array('import', $fields), function($query) {
                $query->with('import:id,name');
            })

            // Pipedrive
            ->when(in_array('pipedrive-accounts', $fields), function($query) {
                $query->with('pipedriveAccounts:id,name');
            })

            // Creator
            ->when(in_array('creator', $fields), function($query) {
                $query->with('creator:id,name');
            })

            ->filter($filters)

            ->when($sortBy && $sortOrder, function($query) use($sortBy, $sortOrder) {
                $query->orderBy($sortBy, $sortOrder);
            })
            ->skip(($request->input('page', 1) - 1) * $count)
            ->paginate($count);

        $data->setCollection($data
            ->map(function($data) use($fields) {
                if ($data->meta) {
                    $meta = array_filter($data->meta, function($key) use($fields) {
                        return in_array("meta->$key", $fields);
                    }, ARRAY_FILTER_USE_KEY);

                    if (empty($meta)) {
                        unset($data->meta);
                    } else {
                        $data->meta = $meta;
                    }
                }

                return $data;
            })
        );

        return $data;
    }
}
