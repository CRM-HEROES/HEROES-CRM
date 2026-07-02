<?php

namespace App\Filters;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProspectFilters extends Filters
{

    /**
     * To apply before all filters
     */
    /*protected function beforeFilters(Builder $builder) {
        if (auth()->user()) {
            $this->scopeUser($builder, auth()->id());
        }
    }*/

    /**
     *
     */
    public function scopeUser(Builder $builder, $userId) {
        $user = request()->project->users()->where('id', $userId)->first();

        if (!$user) {
            return;
        }

        // If user does not have
        // permission to view all prospects
        // in current project,
        $builder->when($user->can('prospectView', request()->project), function($query) use($user) {
            $query->where(function($query) use($user) {
                // Then show only:
                // - prospects created by the user
                // - or prospects affected to the user
                $query
                    ->where('creator_id', $user->id)
                    ->orWhereHas('users', function($query) use($user) {
                        $query->where('id', $user->id);
                    });
            });
        });
    }

    /**
     * Filter by search query
     */
    public function projectQuery(Builder $builder, $value, $project) {
        $values = explode(' ', preg_replace('/[\\s|\\+|-]+/', ' ', $value));

        return $builder->where(function($query) use($values, $project) {
            // Project searchable default fields
            $defaultFields = $project
                ->fields()
                ->where('searchable', true)
                ->where('for', "prospect")
                ->where('meta', false)
                ->get()
                ->pluck('slug');
            // Project searchable meta fields
            $metaFields = $project
                ->fields()
                ->where('searchable', true)
                ->where('for', "prospect")
                ->where('meta', true)
                ->get()
                ->pluck('slug');

            foreach ($values as $value) {
                $value = strtolower(trim(str_replace("+", " ", $value)));

                $query->where(function($query) use($defaultFields, $metaFields, $value) {
                    foreach ($defaultFields as $field) {
                        if ($value) {
                            $query->orWhere(DB::raw("LOWER(`$field`)"), 'LIKE', "%{$value}%");
                        } else {
                            $query->orWhere(function($query) use($field) {
                                $query->where($field, '!=', '')->whereNotNull($field);
                            });
                        }
                    }

                    foreach ($metaFields as $field) {
                        if ($value) {
                            $query->orWhere(DB::raw("LOWER(JSON_EXTRACT(meta, '\$.$field'))"), 'LIKE', "%{$value}%");
                        } else {
                            $query->orWhere(function($query) use($field) {
                                $query->where('meta->' . $field, '!=', '')->whereNotNull('meta->' . $field);
                            });
                        }
                    }
                });
            }
        });
    }

    /**
     * Filter by id
     */
    public function ids(Builder $builder, $value) {
        return $builder->whereIn('id', $value);
    }

    /**
     * Filter by project
     */
    public function withProjects(Builder $builder, $value) {
        return $builder->whereIn('project_id', $value);
    }

    /**
     * Filter by all
     */
    public function all(Builder $builder, $value) {
        if ($value) {
            $builder->withTrashed();
        }
    }

    /**
     * Filter by trashed
     */
    public function trashed(Builder $builder, $value) {
        if ($value) {
            $builder->onlyTrashed();
        }
    }

    /**
     * Filter by processed
     */
    public function processed(Builder $builder, $value) {
        if ($value) {
            $builder->whereNotNull('processed_at');
        } else {
            $builder->whereNull('processed_at');
        }
    }

    /**
     * Filter by processed by
     */
    public function processedBy(Builder $builder, $value) {
        return $builder->whereIn('processed_by', $value);
    }

    /**
     * Filter by processed after
     */
    public function processedAfter(Builder $builder, $value) {
        return $builder->where('processed_at', '>=', $value);
    }

    /**
     * Filter by processed before
     */
    public function processedBefore(Builder $builder, $value) {
        return $builder->where('processed_at', '<=', $value);
    }

    /**
     * Filter by created at
     */
    public function createdAt(Builder $builder, $value) {
        return $builder->whereBetween('created_at', [$value . ' 00:00:00', $value . ' 23:59:59']);
    }

    /**
     * Filter by created after
     */
    public function createdAfter(Builder $builder, $value) {
        return $builder->where('created_at', '>=', $value);
    }

    /**
     * Filter by created before
     */
    public function createdBefore(Builder $builder, $value) {
        return $builder->where('created_at', '<=', $value);
    }

    /**
     * Filter by created X days ago
     */
    public function createdDaysAgo(Builder $builder, $value) {
        return $builder->where('created_at', '>=', \Carbon\Carbon::now()->subDays($value)->format("Y-m-d h:i:s"));
    }

    /**
     * Filter by created before X days
     */
    public function createdBeforeDays(Builder $builder, $value) {
        return $builder->where('created_at', '<=', \Carbon\Carbon::now()->subDays($value)->format("Y-m-d h:i:s"));
    }

    /**
     * Filter by updated at
     */
    public function updatedAt(Builder $builder, $value) {
        return $builder->whereBetween('updated_at', [$value . ' 00:00:00', $value . ' 23:59:59']);
    }

    /**
     * Filter by updated after
     */
    public function updatedAfter(Builder $builder, $value) {
        return $builder->where('updated_at', '>=', $value);
    }

    /**
     * Filter by updated before
     */
    public function updatedBefore(Builder $builder, $value) {
        return $builder->where('updated_at', '<=', $value);
    }

    /**
     * Filter by updated X days ago
     */
    public function updatedDaysAgo(Builder $builder, $value) {
        return $builder->where('updated_at', '>=', \Carbon\Carbon::now()->subDays($value)->format("Y-m-d h:i:s"));
    }

    /**
     * Filter by updated before X days
     */
    public function updatedBeforeDays(Builder $builder, $value) {
        return $builder->where('updated_at', '<=', \Carbon\Carbon::now()->subDays($value)->format("Y-m-d h:i:s"));
    }

    /**
     * Filter by valid address
     */
    public function validAddress(Builder $builder, $value) {
        if ($value == -1) {
            return $builder->whereNull('valid_address');
        }

        return $builder->where('valid_address', $value);
    }

    /**
     * Filter by valid address
     */
    public function unknownAddress(Builder $builder) {
        return $builder->where(function($query) {
            $query
                ->where('valid_address', 0)
                ->orWhereNull('valid_address');
        });
    }

    /**
     * Filter by import
     */
    public function imports(Builder $builder, $value) {
        return $builder->whereIn('import_id', $value);
    }

    /**
     * Search by having users
     */
    public function withUsers($builder, $value) {
        return $builder->whereHas('users', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Search by messages users assigned to
     */
    public function withThreads($builder, $value) {
        return $builder->whereHas('messages', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $treadFilters = new ThreadMessageFilters();

                foreach ($value as $k => $v) {
                    $treadFilters->{$k}($query, $v);
                }
            });
        });
    }


    /**
     * Search by messages users that are not assigned to
     */
    public function withoutThreads($builder, $value) {
        return $builder->whereHas('messages', function($query) use($value) {
            $query->whereDoesntHave('users', function($query) use($value) {
                $query->whereNull('user_message.archived_at');
                $query->when(!empty($value), function($query) use($value) {
                    $query->whereIn('id', $value);
                });
            });
        });
    }

    /**
     * Search by not having users
     */
    public function withoutUsers($builder, $value) {
        return $builder->whereDoesntHave('users', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Search by having waiting users
     */
    public function waitingUsers($builder, $value) {
        return $builder->whereHas('messages', function($query) use($value) {
            $query->whereHas('users', function($query) use($value) {
                $query->whereNull('user_message.archived_at');
                $query->when(!empty($value), function($query) use($value) {
                    $query->whereIn('id', $value);
                });
            });
        });
    }

    /**
     * Search by having waiting users archived
     */
    public function archivedWaitingUsers($builder, $value) {
        return $builder->whereHas('messages', function($query) use($value) {
            $query->whereHas('users', function($query) use($value) {
                $query->whereNotNull('user_message.archived_at');
                $query->when(!empty($value), function($query) use($value) {
                    $query->whereIn('id', $value);
                });
            });
        });
    }

    /**
     * Search by having creators
     */
    public function withCreators($builder, $value) {
        return $builder->whereHas('creator', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Search by not having creators
     */
    public function withoutCreators($builder, $value) {
        return $builder->whereDoesntHave('creator', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Search by having groups
     */
    public function withGroups($builder, $value) {
        return $builder->whereHas('groups', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Search by not having groups
     */
    public function withoutGroups($builder, $value) {
        return $builder->whereDoesntHave('groups', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Search by having imports
     */
    public function withImports($builder, $value) {
        return $builder->whereHas('import', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Search by not having imports
     */
    public function withoutImports($builder, $value) {
        return $builder->whereDoesntHave('import', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Search by folder having files
     */
    public function withFiles($builder, $value) {
        return $builder->whereHas('files', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('folder_id', $value);
            });
        });
    }

    /**
     * Search by folder not having files
     */
    public function withoutFiles($builder, $value) {
        return $builder->whereDoesntHave('files', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('folder_id', $value);
            });
        });
    }

    /**
     * Search by thread having messages
     */
    public function withMessages($builder, $value) {
        return $builder->whereHas('messages', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('thread_id', $value);
            });
        });
    }

    /**
     * Search by thread not having messages
     */
    public function withoutMessages($builder, $value) {
        return $builder->whereDoesntHave('messages', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('thread_id', $value);
            });
        });
    }

    /**
     * Search by having interactions
     */
    public function withInteractions($builder, $value) {
        return $builder->whereHas('interactions', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('source', $value);
            });
        });
    }

    /**
     * Search by not having interactions
     */
    public function withoutInteractions($builder, $value) {
        return $builder->whereDoesntHave('interactions', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('source', $value);
            });
        });
    }

    /**
     * Search by having pipedrive accounts
     */
    public function withPipedriveAccounts($builder, $value) {
        return $builder->whereHas('pipedriveAccounts', function($query) use($value) {
            $query->where('pipedrive_person.from_pipedrive', 1);
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Search by not having pipedrive accounts
     */
    public function withoutPipedriveAccounts($builder, $value) {
        return $builder->whereDoesntHave('pipedriveAccounts', function($query) use($value) {
            $query->where('pipedrive_person.from_pipedrive', 1);
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Search by having sms
     */
    public function withSms($builder, $value) {
        return $builder->whereHas('sms', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $smsFilter = new SmsFilters();

                foreach ($value as $k => $v) {
                    $smsFilter->{$k}($query, $v);
                }
            });
        });
    }

    /**
     * Search by not having sms
     */
    public function withoutSms($builder, $value) {
        return $builder->whereDoesntHave('sms', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('source', $value);
            });
        });
    }

    /**
     * Search by order having product
     */
    public function withOrders($builder, $value) {
        return $builder->whereHas('orders', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $orderFilter = new OrderFilters();

                foreach ($value as $k => $v) {
                    $orderFilter->{$k}($query, $v);
                }
            });
        });
    }

    /**
     * Search by order not having product
     */
    public function withoutOrders($builder, $value) {
        return $builder->whereDoesntHave('orders', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $orderFilter = new OrderFilters();

                foreach ($value as $k => $v) {
                    $orderFilter->{$k}($query, $v);
                }
            });
        });
    }

    /**
     * Search by event
     */
    public function withEvents($builder, $value) {
        return $builder->whereHas('events', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $eventFilter = new EventFilters();

                foreach ($value as $k => $v) {
                    $eventFilter->{$k}($query, $v);
                }
            });
        });
    }

    /**
     * Search by event
     */
    public function withoutEvents($builder, $value) {
        return $builder->whereDoesntHave('events', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $eventFilter = new EventFilters();

                foreach ($value as $k => $v) {
                    $eventFilter->{$k}($query, $v);
                }
            });
        });
    }

    /**
     * Search by having pipelines
     */
    public function withPipelines($builder, $value) {
        return $builder->whereHas('labels', function($query) use($value) {
            $labels = DB::table('pipeline_label')->whereIn('pipeline_id', $value)->get(['label_id'])->pluck('label_id')->toArray();
            $query->whereIn('id', $labels);
        });
    }

    /**
     * Search by not having pipelines
     */
    public function withoutPipelines($builder, $value) {
        return $builder->whereDoesntHave('labels', function($query) use($value) {
            $labels = DB::table('pipeline_label')->whereIn('pipeline_id', $value)->get(['label_id'])->pluck('label_id')->toArray();
            $query->whereIn('id', $labels);
        });
    }

    /**
     * Search by position
     */
    public function position($builder, $value) {
        // $value must contain 3 values
        $value = explode(',', $value);
        if (count($value) != 3) {
            return;
        }

        // Retrieve lat, lng and distance from value
        list($lat, $lng, $km) = $value;

        // lat, lng and distance shoul be integer or float number
        $pattern = "/^-?\\d+(.\\d+)?$/";
        if (!preg_match($pattern, $lat) || !preg_match($pattern, $lng) || !preg_match($pattern, $km)) {
            return $builder;
        }

        $raw = "(111.111 *" .
            " DEGREES(ACOS(LEAST(1.0, COS(RADIANS(?))" .
            " * COS(RADIANS(prospects.latitude))" .
            " * COS(RADIANS(? - prospects.longitude))" .
            " + SIN(RADIANS(?))" .
            " * SIN(RADIANS(prospects.latitude)))))) <= ?";

        return $builder->whereNotNull("latitude")->whereNotNull("longitude")->whereRaw($raw, [$lat, $lng, $lat, $km]);
    }

    /**
     * Search by polygon
     */
    public function polygon($builder, $value) {
        $matches = [];
        $re = '/^(?:\s*-?\d+(?:\.\d+)?\s+-?\d+(?:\.\d+)?\s*,)*\s*-?\d+(?:\.\d+)?\s+-?\d+(?:\.\d+)?\s*$/m';

        preg_match_all($re, $value, $matches, PREG_SET_ORDER, 0);

        if (count($matches) == 0) {
            return;
        }

        $raw = "ST_CONTAINS(ST_GEOMFROMTEXT('POLYGON(($value))'), POINT(latitude, longitude))";

        return $builder->where(DB::raw($raw), true);
    }

    /**
     * Search by with position
     */
    public function withPosition($builder, $value) {
        if ($value) {
            return $builder->whereNotNull("latitude")->whereNotNull("longitude");
        }

        return $builder->where(function($query){
            $query
                ->whereNull("latitude")
                ->orWhereNull("longitude");;
        });
    }

    /**
     * Do not use
     * when using pagination
     *
     * Count by sms
     */
    public function smsCount($builder, $value)
    {
        if (!is_array($value) && !isset($value['count'])) {
            return;
        }

        return $builder->withCount(['sms' => function($query) use($value) {
            foreach ($value as $k => $v) {
                if ($k == 'sources' && !empty($v)) {
                    $query->whereIn('source', $v);
                } else if ($k == 'withCreators' && !empty($v)) {
                    $query->whereIn('creator_id', $v);
                }
            }
        }])->having('sms_count', $value['count']);
    }

    /**
     * Do  not use
     * when using pagination
     *
     * Count by interactions
     */
    public function interactionsCount($builder, $value)
    {
        if (!is_array($value) && !isset($value['count'])) {
            return;
        }

        return $builder->withCount(['interactions' => function($query) use($value) {
            foreach ($value as $k => $v) {
                if ($k == 'sources') {
                    $query->whereIn('source', $v);
                } else if ($k == 'withCreators') {
                    $query->whereIn('creator_id', $v);
                }
            }
        }])->having('interactions_count', $value['count']);
    }

    /**
     * Search by default field
     */
    public function defaultField($builder, $slug, $value) {
        $value = Str::lower($value);
        $value = Str::replace('"', '\\\\\\\\"', $value);
        // return $builder->where(DB::raw("LOWER(`$slug`)"), 'LIKE', "%{$value}%");
        if ($slug == "postal_code") {
            return $builder->where(DB::raw("LOWER(`$slug`)"), 'LIKE', "{$value}%");
        }

        return $builder->where(DB::raw("LOWER(`$slug`)"), 'LIKE', "%{$value}%");
    }

    /**
     * Search by meta field
     */
    public function metaField($builder, $slug, $value) {
        $value = Str::lower($value);
        $value = Str::replace('"', '\\\\\\\\"', $value);
        // dd($value);
        return $builder->whereRaw("LOWER(JSON_EXTRACT(meta, \"\$.$slug\")) LIKE '%{$value}%'");
    }

    /**
     * Search by default field
     */
    public function withDefaultField($builder, $slug, $value) {
        if ($value) {
            $builder->where(function($query) use ($slug) {
                $query->whereRaw("`$slug` <> '' AND `$slug` IS NOT NULL");
            });
        } else {
            $builder->where(function($query) use ($slug) {
                $query->whereRaw("`$slug` = '' OR `$slug` IS NULL");
            });
        }
    }

    /**
     * Search by meta field
     */
    public function withMetaField($builder, $slug, $value) {
        if ($value) {
            $builder->where(function($query) use ($slug) {
                $query->whereRaw("JSON_EXTRACT(meta, \"\$.$slug\") <> '' AND JSON_EXTRACT(meta, \"\$.$slug\") IS NOT NULL");
            });
        } else {
            $builder->where(function($query) use ($slug) {
                $query->whereRaw("JSON_EXTRACT(meta, \"\$.$slug\") = '' OR JSON_EXTRACT(meta, \"\$.$slug\") IS NULL");
            });
        }
    }

    /**
     * Search by label
     */
    public function withCategoryLabel($builder, $categoryId, $value) {
        return $builder->whereHas('labels', function($query) use($categoryId, $value) {
            $query
                ->where('category_id', $categoryId)
                ->when(!empty($value), function($query) use($value) {
                    $query->whereIn('id', $value);
                });
        });
    }

    /**
     * Search by without label
     */
    public function withoutCategoryLabel($builder, $categoryId, $value) {
        return $builder->whereDoesntHave('labels', function($query) use($categoryId, $value) {
            $query
                ->where('category_id', $categoryId)
                ->when(!empty($value), function($query) use($value) {
                    $query->whereIn('id', $value);
                });
        });
    }

    /**
     * Search by thread message
     */
    public function threadMessage($builder, $threadId, $value) {
        return $builder->whereHas('messages', function($query) use($threadId, $value) {
            $query
                ->where('thread_id', $threadId)
                ->where('plain_text', 'LIKE', "%{$value}%");
        });
    }

    /**
     * Search by label
     */
    public function withLabels($builder, $value) {
        return $builder->whereHas('labels', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Search by without label
     */
    public function withoutLabels($builder, $value) {
        return $builder->whereDoesntHave('labels', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
        });
    }

    /**
     * Filter by search query
     */
    public function query(Builder $builder, $value) {
        if (!request()->project) {
            return;
        }

        return $this->projectQuery($builder, $value, request()->project);
    }

    /**
     * Dynamic function call
     */
    public function __call($method, $args)
    {
        // Default field search
        if (Str::startsWith($method, 'field_')) {
            return $this->defaultField(
                $args[0],
                Str::replace('field_', '', $method),
                $args[1]
            );
        }

        // Meta field search
        if (Str::startsWith($method, 'meta_')) {
            return $this->metaField(
                $args[0],
                Str::replace('meta_', '', $method),
                $args[1]
            );
        }

        // Default field search
        if (Str::startsWith($method, 'withField_')) {
            return $this->withDefaultField(
                $args[0],
                Str::replace('withField_', '', $method),
                $args[1]
            );
        }

        // Meta field search
        if (Str::startsWith($method, 'withMeta_')) {
            return $this->withMetaField(
                $args[0],
                Str::replace('withMeta_', '', $method),
                $args[1]
            );
        }

        // Label search
        if (Str::startsWith($method, 'withCategory_')) {
            return $this->withCategoryLabel(
                $args[0],
                Str::replace('withCategory_', '', $method),
                $args[1]
            );
        }

        // Label search
        if (Str::startsWith($method, 'withoutCategory_')) {
            return $this->withoutCategoryLabel(
                $args[0],
                Str::replace('withoutCategory_', '', $method),
                $args[1]
            );
        }

        // Thread messages search
        if (Str::startsWith($method, 'thread_')) {
            return $this->threadMessage(
                $args[0],
                Str::replace('thread_', '', $method),
                $args[1]
            );
        }

        throw new \BadMethodCallException("Method $method does not exist");;
    }
}
