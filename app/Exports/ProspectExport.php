<?php

namespace App\Exports;

use App\Filters\ProspectFilters;
use App\Models\Export;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProspectExport implements FromCollection
{
    
    const MAPPING_FIELD_DEFAULT = 0;
    const MAPPING_FIELD_META = 1;
    const MAPPING_FIELD_LABEL = 2;
    const MAPPING_FIELD_EVENT = 3;
    const MAPPING_FIELD_MESSAGE = 4;
    const MAPPING_FIELD_ORDER = 5;
    const MAPPING_FIELD_SMS = 6;
    const MAPPING_FIELD_INTERACTION = 7;
    const MAPPING_FIELD_LINK = 8;
    const MAPPING_FIELD_USER = 9;
    const MAPPING_FIELD_GROUP = 10;

    
    protected Export $export;
    protected $columns;
    protected $limit;
    protected $skip;
    protected $count = 0;

    public function __construct(Export $export, $limit = 1000, $skip = 0)
    {
        $this->export = $export;
        $this->limit = $limit;
        $this->skip = $skip;
        $this->columns = $this->getColumns();
    }

    /**
     * 
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * 
     */
    public function collection()
    {
        return $this->body()->prepend($this->header());
    }

    /**
     * 
     */
    protected function header()
    {
        return array_map(function($column) {
            switch ($column['type']) {
                case ProspectExport::MAPPING_FIELD_DEFAULT:
                    return $column['field']->name;
                case ProspectExport::MAPPING_FIELD_META:
                    return $column['field']->name;
                case ProspectExport::MAPPING_FIELD_LABEL:
                    return $column['field']->name;
                case ProspectExport::MAPPING_FIELD_MESSAGE:
                    return $column['field']->name;
                case ProspectExport::MAPPING_FIELD_USER:
                    return trans('prospect.export.assigned_users');
                case ProspectExport::MAPPING_FIELD_GROUP:
                    return trans('prospect.export.assigned_groups');
                case ProspectExport::MAPPING_FIELD_EVENT:
                    return trans('prospect.export.events');
                case ProspectExport::MAPPING_FIELD_SMS:
                    return trans('prospect.export.sms');
                case ProspectExport::MAPPING_FIELD_INTERACTION:
                    return trans('prospect.export.interaction');
                case ProspectExport::MAPPING_FIELD_ORDER:
                    return trans('prospect.export.orders');
                case ProspectExport::MAPPING_FIELD_LINK:
                    return trans('prospect.export.links');
                default:
                    return "";
            }
        }, $this->columns);
    }
    
    /**
     * 
     */
    protected function body()
    {
        $prospects = $this->getProspects();
        $this->count = $prospects->count();

        return $prospects->map(function($prospect) {
            return array_map(function($column) use($prospect) {
                switch ($column['type']) {
                    case ProspectExport::MAPPING_FIELD_DEFAULT:
                        return isset($prospect->{$column['field']->slug}) ? $prospect->{$column['field']->slug} : "";
                    case ProspectExport::MAPPING_FIELD_META:
                        return isset($prospect->meta[$column['field']->slug]) ? $prospect->meta[$column['field']->slug] : "";
                    case ProspectExport::MAPPING_FIELD_LABEL:
                        return $this->exportLabels($prospect, $column['field']->id);
                    case ProspectExport::MAPPING_FIELD_MESSAGE:
                        return $this->exportMessages($prospect, $column['field']->id);
                    case ProspectExport::MAPPING_FIELD_USER:
                        return $this->exportUsers($prospect);
                    case ProspectExport::MAPPING_FIELD_GROUP:
                        return $this->exportGroups($prospect);
                    case ProspectExport::MAPPING_FIELD_EVENT:
                        return $this->exportEvents($prospect);
                    case ProspectExport::MAPPING_FIELD_SMS:
                        return $this->exportSms($prospect);
                    case ProspectExport::MAPPING_FIELD_INTERACTION:
                        return $this->exportInteractions($prospect);
                    case ProspectExport::MAPPING_FIELD_ORDER:
                        return $this->exportOrders($prospect);
                    case ProspectExport::MAPPING_FIELD_LINK:
                        return $this->exportLinks($prospect);
                    default:
                        return "";
                }
            }, $this->columns);
        });
    }

    /**
     * 
     */
    protected function getColumns()
    {
        return array_filter(array_map(function($field) {
            // Labels
            if (Str::startsWith($field, 'category->')) {
                $categoryId = str_replace('category->', '', $field);

                // Check if category exists
                $category = $this->export->project->categories->where('id', $categoryId)->first();
                if (!$category) {
                    // else, do not map the column
                    return null;
                }

                return [
                    'type'  => ProspectExport::MAPPING_FIELD_LABEL,
                    'field' => $category
                ];
            }

            // Thread
            else if (Str::startsWith($field, 'thread->')) {
                $threadId = str_replace('thread->', '', $field);

                // Check if thread exists
                $thread = $this->export->project->threads->where('id', $threadId)->first();
                if (!$thread) {
                    // else, do not map the column
                    return null;
                }

                return [
                    'type'  => ProspectExport::MAPPING_FIELD_MESSAGE,
                    'field' => $thread
                ];
            }

            // Users
            else if ($field == 'users') {
                return [
                    'type'  => ProspectExport::MAPPING_FIELD_USER
                ];
            }

            // Groups
            else if ($field == 'groups') {
                return [
                    'type'  => ProspectExport::MAPPING_FIELD_GROUP
                ];
            }

            // Events
            else if ($field == 'events') {
                return [
                    'type'  => ProspectExport::MAPPING_FIELD_EVENT
                ];
            }
            
            // Sms
            else if ($field == 'sms') {
                return [
                    'type'  => ProspectExport::MAPPING_FIELD_SMS
                ];
            }

            // Interactions
            else if ($field == 'interactions') {
                return [
                    'type'  => ProspectExport::MAPPING_FIELD_INTERACTION
                ];
            }

            // Orders
            else if ($field == 'orders') {
                return [
                    'type'  => ProspectExport::MAPPING_FIELD_ORDER
                ];
            }

            // Links
            else if ($field == 'links') {
                return [
                    'type'  => ProspectExport::MAPPING_FIELD_LINK
                ];
            }

            // Meta
            else if (Str::startsWith($field, 'meta->')) {
                $fieldId = str_replace('meta->', '', $field);

                // Check if field exists
                $field = $this->export->project->fields->where('slug', $fieldId)->first();
                if (!$field || !$field->meta) {
                    // else, do not map the column
                    return null;
                }

                return [
                    'type'  => ProspectExport::MAPPING_FIELD_META,
                    'field' => $field
                ];
                
            // Default field
            } else {
                // Check if field exists
                $field = $this->export->project->fields->where('slug', $field)->first();
                if (!$field || $field->meta) {
                    // else, do not map the column
                    return null;
                }

                return [
                    'type'  => ProspectExport::MAPPING_FIELD_DEFAULT,
                    'field' => $field
                ];
            }
        }, $this->export->fields));
    }

    /**
     * Export prospect users
     */
    protected function exportLabels($prospect, $category)
    {
        return $prospect
            ->labels
            ->filter(function($label) use($category) {
                return $category == $label->category_id;
            })
            ->values()
            ->pluck('name')
            ->implode("\n");
    }

    /**
     * Export prospect users
     */
    protected function exportUsers($prospect)
    {
        return $prospect
            ->users
            ->map(function($user) {
                return [
                    'name'  => $user->name,
                    'email' => $user->email,
                ];
            })
            ->toJson();
    }

    /**
     * Export prospect groups
     */
    protected function exportGroups($prospect)
    {
        return $prospect
            ->groups
            ->map(function($user) {
                return [
                    'name' => $user->name,
                ];
            })
            ->toJson();
    }

    /**
     * Export prospect events
     */
    protected function exportEvents($prospect)
    {
        return $prospect
            ->events
            ->filter(function($event) {
                return $event->calendar;
            })
            ->map(function($event) {
            $user = $event->user ? [
                'name'  => $event->user->name,
                'email' => $event->user->email,
            ] : [];

            return [
                'name' => $event->name,
                'description' => $event->description,
                'started_at'  => $event->started_at,
                'ended_at'    => $event->ended_at,
                'location'    => $event->location,
                'latitude'    => $event->latitude,
                'longitude'   => $event->longitude,
                'done'        => $event->done,
                'confirmed'   => $event->confirmed,
                'calendar'    => [
                    'name' => $event->calendar->name,
                    'type' => $event->calendar->type,
                ],
                'user'        => $user,
            ];
        })->toJson();
    }
    
    /**
     * Export prospect sms
     */
    protected function exportSms($prospect)
    {
        return $prospect
            ->sms
            ->map(function($sms) {
                return [
                    'message'    => $sms->message,
                    'source'     => $sms->source,
                    'created_at' => $sms->created_at->format("Y-m-d h:i:s"),
                    'sent_at'    => $sms->sent_at ? $sms->sent_at->format("Y-m-d h:i:s") : null,
                    'user'       => $sms->creator ? [
                        'name'  => $sms->creator->name,
                        'email' => $sms->creator->email,
                    ] : null,
                ];
            })
            ->toJson();
    }
    
    /**
     * Export prospect interactions
     */
    protected function exportInteractions($prospect)
    {
        return $prospect
            ->interactions
            ->map(function($interaction) {
                return [
                    'source'     => $interaction->source,
                    'created_at' => $interaction->created_at->format("Y-m-d h:i:s"),
                    'user'       => $interaction->creator ? [
                        'name'  => $interaction->creator->name,
                        'email' => $interaction->creator->email,
                    ] : null,
                ];
            })
            ->toJson();
    }
    
    /**
     * Export prospect orders
     */
    protected function exportOrders($prospect)
    {
        return $prospect
            ->orders
            ->map(function($order) {
                return [
                    'date'     => $order->created_at->format("Y-m-d h:i:s"),
                    'products' => $order->products->map(function($product) {
                        return [
                            'name'          => $product->name,
                            'description'   => $product->description,
                            'price'         => $product->price,
                            'including_tax' => $product->including_tax,
                            'tax'           => $product->tax,
                            'currency'      => $product->currency,
                            'pivot'         => [
                                'price'         => $product->pivot->price,
                                'including_tax' => $product->pivot->including_tax,
                                'tax'           => $product->pivot->tax,
                                'currency'      => $product->pivot->currency,
                                'quantity'      => $product->pivot->quantity,
                            ],
                        ];
                    }),
                    'steps'   => $order->steps->map(function($step) {
                        return [
                            'name' => $step->name
                        ];
                    }),
                    'actions' => $order->actions->map(function($action) {
                        return [
                            'action' => [
                                'name' => $action->name,
                                'description' => $action->description,
                            ],
                            'user' => [
                                'name' => $action->pivot->user->name,
                                'email' => $action->pivot->user->email,
                            ],
                            'paid' => $action->pivot->paid
                        ];
                    }),
                ];
            })
            ->toJson();
    }
    
    /**
     * Export prospect messages
     */
    protected function exportMessages($prospect, $thread)
    {
        return $prospect
            ->messages
            ->where('thread_id', $thread)
            ->map(function($message) {
                return [
                    'body'       => $message->body,
                    'created_at' => $message->created_at->format("Y-m-d h:i:s"),
                    'user'       => $message->creator ? [
                        'name'  => $message->creator->name,
                        'email' => $message->creator->email,
                    ] : null,
                ];
            })
            ->toJson();
    }
    
    /**
     * Export prospect links
     */
    protected function exportLinks($prospect)
    {
        return $prospect
            ->links
            ->map(function($link) {
                return [
                    'name' => $link->name,
                    'link' => $link->link,
                ];
            })
            ->toJson();
    }

    /**
     * 
     */
    protected function getFilters()
    {
        $filters = new ProspectFilters();

        $filters->addFilter('createdBefore', $this->export->created_at->format("Y-m-d H:i:s"));

        // Scope user
        if ($this->export->user && !$this->export->user->can('prospectView', $this->export->project)) {
            $filters->addFilter('scopeUser', $this->export->user);
        }

        foreach ($this->export->filters as $key => $value) {
            $filters->addFilter($key, $value);
        }

        return $filters;
    }
    
    /**
     * Get prospects
     */
    protected function getProspects()
    {
        // Sort By
        $sortBy = $this->export->sort_by;
        if (
            !$this->export->project
                ->fields()
                ->where('slug', Str::replace('meta->', '', $sortBy))
                ->where('meta', Str::startsWith($sortBy, 'meta->'))
                ->first()
        ) {
            $sortBy = "id";
        }

        // Sort Order
        $sortOrder = $this->export->sort_order;
        if (in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = "desc";
        }

        // Categories in which we select labels associated to prospects
        $categories = array_map(function($field) {
            return Str::replace('category->', '', $field);
        }, array_filter($this->export->fields, function($field) {
            return Str::startsWith($field, 'category->');
        }));
        
        // Threads in which we select messages associated to prospects
        $threads = array_map(function($field) {
            return Str::replace('thread->', '', $field);
        }, array_filter($this->export->fields, function($field) {
            return Str::startsWith($field, 'thread->');
        }));
        
        return $this->export->project
            ->prospects()
            ->when(!empty($categories), function($query) use($categories) {
                $query->with([
                    'labels' => function($query) use($categories) {
                        $query->select('id', 'name', 'category_id')
                            ->whereIn('category_id', $categories);
                    }
                ]);
            })
            ->when(!empty($threads), function($query) use($threads) {
                $query->with([
                    'messages' => function($query) use($threads) {
                        $query->select('id', 'prospect_id', 'creator_id', 'thread_id', 'body', 'created_at')
                            ->whereIn('thread_id', $threads);
                    },
                    'messages.creator' => function($query) {
                        $query->select('id', 'name', 'email');
                    },
                ]);
            })
            ->when(in_array('events', $this->export->fields), function($query) {
                $query->with([
                    'events' => function($query) {
                        $query->select('id', 'prospect_id', 'calendar_id', 'user_id', 'name', 'description', 'started_at', 'ended_at', 'location', 'latitude', 'longitude', 'done', 'confirmed');
                    },
                    'events.calendar' => function($query) {
                        $query->select('id', 'name', 'type');
                    },
                    'events.user' => function($query) {
                        $query->select('id', 'name', 'email');
                    }
                ]);
            })
            ->when(in_array('sms', $this->export->fields), function($query) {
                $query->with([
                    'sms' => function($query) {
                        $query->select('id', 'prospect_id', 'creator_id', 'message', 'source', 'sent_at', 'created_at');
                    },
                    'sms.creator' => function($query) {
                        $query->select('id', 'name', 'email');
                    },
                ]);
            })
            ->when(in_array('orders', $this->export->fields), function($query) {
                $query->with([
                    'orders' => function($query) {
                        $query->select('id', 'prospect_id', 'created_at');
                    },
                    'orders.products' => function($query) {
                        // $query->select('id', 'name', 'description', 'price', 'tax_percent', 'including_tax', 'currency');
                    },
                    'orders.actions' => function($query) {
                        // $query->select('id', 'order_id', 'actor_id', 'order_action_id', 'paid');
                    },
                    'orders.steps' => function($query) {
                        $query->select('id', 'name');
                    },
                ]);
            })
            ->when(in_array('users', $this->export->fields), function($query) {
                $query->with('users:id,name,email');
            })
            ->when(in_array('groups', $this->export->fields), function($query) {
                $query->with('groups:id,name');
            })
            ->when(in_array('links', $this->export->fields), function($query) {
                $query->with('links:id,prospect_id,name,link');
            })
            ->orderBy($sortBy, $sortOrder)
            ->skip($this->skip)
            ->limit($this->limit)
            ->filter($this->getFilters())
            ->get();
    }
}