<?php
  
namespace App\Observers;

use App\Events\ProjectUserAttached;
use App\Models\Project;
use App\Jobs\ProjectGetLatLng;
use App\Models\Category;
use App\Models\Permission;
use App\Models\UserSetting;
use App\Utils\Field\Field;
use App\Utils\ProjectSetting;
use Illuminate\Support\Str;
  
class ProjectObserver
{
    /**
     * Handle the Project "creating" event.
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    public function creating(Project $project)
    {
        // Slug
        $project->slug = $this->getAvailableSlug($project);
        // dd($project->slug);
    }

    /**
     * Handle the Project "created" event.
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    public function created(Project $project)
    {
        $this->addDefaultFields($project);

        if ($project->type != "demo") {
            $this->associateToCurrentUser($project);
            $this->addProspects($project);
        }
    
        $category = $this->addCategoryLabels($project);
        $this->setProjectProspectsTableSetting($project, $category);
        ProjectUserAttached::dispatch($project, $project->creator);
        $this->getLatLng($project);
    
        if (!$this->jobCategoryConfiguration($project)) {
        
            $this->addCalendars($project);
            $this->addFolders($project);
            $this->addThreads($project);
            $this->addOrderSteps($project);
            $this->addOrderActions($project);
            $this->addRoles($project);

        }
    }

    /**
     * Handle the Project "updated" event.
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    public function updated(Project $project)
    {
        $this->getLatLng($project);
    }

    /**
     * Get available slug
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    protected function getAvailableSlug(Project &$project)
    {
        $defaultSlug = Str::slug($project->name, '_');

        for ($i = 1;; ++$i) {
            $slug = $i == 1 ? 
                $defaultSlug : 
                $defaultSlug . "_" . $i;

            // Skip if slug already belongs to another project
            if (Project::where('slug', $slug)->first()) {
                continue;
            }

            // dd(Project::where('slug', $slug)->first(), $slug . "_ok");
            // Available slug
            return $slug;
        }
    }

    /**
     * Get project latitude
     * and longitude
     * from street, postal code, city and country info
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    protected function getLatLng(Project &$project)
    {
        if (
            !$project->isDirty('street') &&
            !$project->isDirty('street_bis') &&
            !$project->isDirty('postal_code') &&
            !$project->isDirty('city') &&
            !$project->isDirty('country')
        ) {
            return;
        }

        $project->syncOriginal(['street', 'street_bis', 'postal_code', 'city', 'country']);
        ProjectGetLatLng::dispatchAfterResponse($project);
    }

    /**
     * Attach current user to the project
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    protected function associateToCurrentUser(Project &$project)
    {
        if (!$project->creator_id) {
            return;
        }

        $project->users()->syncWithoutDetaching([auth()->id() => [
            'creator_id' => $project->creator_id,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]]);
    }

    /**
     * Add prospects to project
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    protected function addProspects(Project &$project)
    {
        $project->prospects()->createMany(array_fill(0, 10, []));
    }
    
    /**
     * Add default fields to project
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    protected function addDefaultFields(Project &$project)
    {
        $excludeFields = ['fax_number', 'job_title', 'company', 'website', 'country', 'state', 'county', 'latitude', 'longitude', 'ip_address', 'updated_at'];
        $searchableFields = ['first_name', 'last_name'];

        $fields = array_filter(array_map(function($field) use($searchableFields) {
            return array_merge(
                $field, [
                    // Check if the field is searchable
                    'searchable' => in_array($field['slug'], $searchableFields)
                ]
            );
        }, Field::fields()), function($field) use($excludeFields) {
            return $field['for'] != 'prospect' || !in_array($field['slug'], $excludeFields);
        });

        $project->fields()->createMany($fields);
    }
    
    /**
     * Set user prospects table setting
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    protected function setProjectProspectsTableSetting(Project &$project, Category $category)
    {
        $setting = array_values($project->fields->filter(function($field) {
            return $field['for'] == 'prospect';
        })->map(function($field) {
            return [
                'key' => ($field->meta ? 'meta->' : '') . $field->slug
            ];
        })->toArray());

        $setting = array_merge(
            [
                ['key' => 'category->' . $category->id, "fixed" => true], 
                ['key' => 'events', 'width' => 125]
            ], 
            $setting,
            [
                ['key' => 'users']
            ], 
        );

        ProjectSetting::set($project, 'prospects-table', $setting);
    }
    
    /**
     * Add default calendars to project
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    protected function addCalendars(Project &$project)
    {
        $calendars = [
            [
                'name' => 'RDV physique commercial',
                'description' => 'RDV physique commercial',
                'color' => '#0a4047',
                'bgcolor' => '#98f9fb',
                'type' => 'physical',
            ],
            [
                'name' => 'RDV physique installation',
                'description' => 'RDV physique installation',
                'color' => '#272202',
                'bgcolor' => '#ffef8a',
                'type' => 'physical',
            ],
            [
                'name' => 'RDV Tel Télépro',
                'description' => 'RDV Tel Télépro',
                'color' => '#FFFFFF',
                'bgcolor' => '#cc386c',
                'type' => 'telephone',
            ],
            [
                'name' => 'RDV Tel commercial',
                'description' => 'RDV Tel commercial',
                'color' => '#FFFFFF',
                'bgcolor' => '#2ABB47',
                'type' => 'telephone',
            ],
            [
                'name' => 'RDV Tel contrôle qualité',
                'description' => 'RDV Tel contrôle qualité',
                'color' => '#FFFFFF',
                'bgcolor' => '#2BA6C5',
                'type' => 'telephone',
            ],
            [
                'name' => 'RDV Hangout',
                'description' => 'RDV Hangout',
                'color' => '#FFFFFF',
                'bgcolor' => '#E69100',
                'type' => 'hangout',
            ],
            [
                'name' => 'Tâche',
                'description' => 'Tâche',
                'color' => '#FFFFFF',
                'bgcolor' => '#1aa287',
                'type' => 'task',
            ],
            [
                'name' => 'Lieu de départ',
                'description' => 'Lieu de départ',
                'color' => '#FFFFFF',
                'bgcolor' => '#8C5B17',
                'type' => 'physical',
            ],
            [
                'name' => 'Lien d\'arrivée',
                'description' => 'Lien d\'arrivée',
                'color' => '#FFFFFF',
                'bgcolor' => '#1F6FEF',
                'type' => 'physical',
            ],
        ];

        $project->calendars()->createMany($calendars);
    }
    
    /**
     * Add default labels to project
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    protected function addCategoryLabels(Project $project)
    {
        $category = $project->categories()->create([
            'name' => trans('label.category.status.name'),
        ]);

        if ($project->private) {
            $labels = [
                [
                    "name" => trans('label.category.status.labels.family'),
                    "color" => "#ffffff",
                    "bgcolor" => "#3097d1",
                ],
                [
                    "name" => trans('label.category.status.labels.friend'),
                    "color" => "#ffffff",
                    "bgcolor" => "#3097d1",
                ],
                [
                    "name" => trans('label.category.status.labels.work'),
                    "color" => "#ffffff",
                    "bgcolor" => "#ef531f",
                ],
                [
                    "name" => trans('label.category.status.labels.association'),
                    "color" => "#ffffff",
                    "bgcolor" => "#3097d1",
                ],
                [
                    "name" => trans('label.category.status.labels.holiday'),
                    "color" => "#ffffff",
                    "bgcolor" => "#3097d1",
                ],
                [
                    "name" => trans('label.category.status.labels.religion'),
                    "color" => "#ffffff",
                    "bgcolor" => "#3097d1",
                ],
                [
                    "name" => trans('label.category.status.labels.provider'),
                    "color" => "#231f1f",
                    "bgcolor" => "#39ea4d",
                ],
                [
                    "name" => trans('label.category.status.labels.acquaintance'),
                    "color" => "#FFFFFF",
                    "bgcolor" => "#000000",
                ],
            ];
        } else {
            $labels = [
                [
                    "name" => trans('label.category.status.labels.new_lead'),
                    "color" => "#1d1b1b",
                    "bgcolor" => "#b5d31d",
                    "default" => true,
                ],
                [
                    "name" => trans('label.category.status.labels.wait_for_funding'),
                    "color" => "#000000",
                    "bgcolor" => "#ffff00",
                    "default" => true,
                ],
                [
                    "name" => trans('label.category.status.labels.hold_first_call'),
                    "color" => "#ffffff",
                    "bgcolor" => "#3097d1",
                ],
                [
                    "name" => trans('label.category.status.labels.new_distributor'),
                    "color" => "#ffffff",
                    "bgcolor" => "#83903D",
                ],
                [
                    "name" => trans('label.category.status.labels.to_recall'),
                    "color" => "#ffffff",
                    "bgcolor" => "#c05ee4",
                ],
                [
                    "name" => trans('label.category.status.labels.wait_for_answer'),
                    "color" => "#ffffff",
                    "bgcolor" => "#FF9900",
                ],
                [
                    "name" => trans('label.category.status.labels.do_not_answer_1'),
                    "color" => "#ffffff",
                    "bgcolor" => "#3097d1",
                ],
                [
                    "name" => trans('label.category.status.labels.do_not_answer_2'),
                    "color" => "#ffffff",
                    "bgcolor" => "#3097d1",
                ],
                [
                    "name" => trans('label.category.status.labels.do_not_answer_3'),
                    "color" => "#100f0f",
                    "bgcolor" => "#39b021",
                ],
                [
                    "name" => trans('label.category.status.labels.sms_sent_1'),
                    "color" => "#FFFFFF",
                    "bgcolor" => "#4f84c9",
                ],
                [
                    "name" => trans('label.category.status.labels.sms_sent_2'),
                    "color" => "#FFFFFF",
                    "bgcolor" => "#4f84c9",
                ],
                [
                    "name" => trans('label.category.status.labels.sms_sent_3'),
                    "color" => "#FFFFFF",
                    "bgcolor" => "#4f84c9",
                ],
                [
                    "name" => trans('label.category.status.labels.wait_for_his_call'),
                    "color" => "#ffffff",
                    "bgcolor" => "#3097d1",
                ],
                [
                    "name" => trans('label.category.status.labels.appointment_in_progress'),
                    "color" => "#ffffff",
                    "bgcolor" => "#3097d1",
                ],
                [
                    "name" => trans('label.category.status.labels.send_quote'),
                    "color" => "#ffffff",
                    "bgcolor" => "#3097d1",
                ],
                [
                    "name" => trans('label.category.status.labels.quote_sent'),
                    "color" => "#000000",
                    "bgcolor" => "#00FFFF",
                ],
                [
                    "name" => trans('label.category.status.labels.signed'),
                    "color" => "#ffffff",
                    "bgcolor" => "#c395d0",
                ],
                [
                    "name" => trans('label.category.status.labels.payment_in_progress'),
                    "color" => "#000000",
                    "bgcolor" => "#e7900e",
                ],
                [
                    "name" => trans('label.category.status.labels.installation_in_progress'),
                    "color" => "#231f1f",
                    "bgcolor" => "#39ea4d",
                ],
                [
                    "name" => trans('label.category.status.labels.make_invoice'),
                    "color" => "#231f1f",
                    "bgcolor" => "#FF00DD",
                ],
                [
                    "name" => trans('label.category.status.labels.invoice_sent'),
                    "color" => "#000000",
                    "bgcolor" => "#FA4AF9",
                ],
                [
                    "name" => trans('label.category.status.labels.paid'),
                    "color" => "#000000",
                    "bgcolor" => "#1FD15D",
                ],
                [
                    "name" => trans('label.category.status.labels.satisfied'),
                    "color" => "#000000",
                    "bgcolor" => "#ffff00",
                ],
                [
                    "name" => trans('label.category.status.labels.out_of_criteria'),
                    "color" => "#ffffff",
                    "bgcolor" => "#3097d1",
                ],
                [
                    "name" => trans('label.category.status.labels.negative'),
                    "color" => "#ffffff",
                    "bgcolor" => "#ef531f",
                ],
                [
                    "name" => trans('label.category.status.labels.wrong_number'),
                    "color" => "#ffffff",
                    "bgcolor" => "#3097d1",
                ],
                [
                    "name" => trans('label.category.status.labels.canceled_contract'),
                    "color" => "#ffffff",
                    "bgcolor" => "#ef531f",
                ],
                [
                    "name" => trans('label.category.status.labels.refusal_of_funding'),
                    "color" => "#ffffff",
                    "bgcolor" => "#d12ecc",
                ],
                [
                    "name" => trans('label.category.status.labels.not_happy'),
                    "color" => "#000000",
                    "bgcolor" => "#787800",
                ],
                [
                    "name" => trans('label.category.status.labels.after_sales_service'),
                    "color" => "#FFFFFF",
                    "bgcolor" => "#d22d2d",
                ]
            ];
        }
        
        $category->labels()->createMany(array_map(function($label, $order) {
            return array_merge($label, ['order' => $order + 1]);
        }, $labels, array_keys($labels)));

        return $category;
    }

    /**
     * Add default folders to project
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    protected function addFolders(Project $project)
    {
        $folders = [
            [
                'name' => trans('folder.contract'),
                'private' => true,
            ],
            [
                'name' => trans('folder.id_card'),
                'private' => true,
            ],
            [
                'name' => trans('folder.iban'),
                'private' => true,
            ],
            [
                'name' => trans('folder.discharge'),
                'private' => true,
            ],
            [
                'name' => trans('folder.invoice'),
                'private' => true,
            ],
        ];

        $project->folders()->createMany(array_map(function($folder, $order) {
            return array_merge($folder, ['order' => $order + 1]);
        }, $folders, array_keys($folders)));
    }

    /**
     * Add default threads to project
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    protected function addThreads(Project $project)
    {
        $threads = [
            [
                'name' => trans('thread.discussion_with_prospect'),
                'private' => false,
            ],
            [
                'name' => trans('thread.internal_discussion'),
                'private' => true,
            ],
        ];

        $project->threads()->createMany(array_map(function($thread, $order) {
            return array_merge($thread, ['order' => $order + 1]);
        }, $threads, array_keys($threads)));
    }

    /**
     * Add default order steps to project
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    protected function addOrderSteps(Project $project)
    {
        $orderSteps = [
            [
                'name' => trans('order-step.reception'),
            ],
            [
                'name' => trans('order-step.processing'),
            ],
            [
                'name' => trans('order-step.preparation'),
            ],
            [
                'name' => trans('order-step.shipping'),
            ],
            [
                'name' => trans('order-step.delivery_tracking'),
            ],
            [
                'name' => trans('order-step.billing'),
            ],
            [
                'name' => trans('order-step.after_sales_service'),
            ],
        ];

        $project->orderSteps()->createMany(array_map(function($orderStep, $order) {
            return array_merge($orderStep, ['order' => $order + 1]);
        }, $orderSteps, array_keys($orderSteps)));
    }

    /**
     * Add default order actions to project
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    protected function addOrderActions(Project $project)
    {
        $orderActions = [
            [
                'name' => trans('order-action.provided_by'),
            ],
            [
                'name' => trans('order-action.qualified_by'),
            ],
            [
                'name' => trans('order-action.appointment_entered_by'),
            ],
            [
                'name' => trans('order-action.contract_entered_by'),
            ],
            [
                'name' => trans('order-action.contract_managed_by'),
            ],
            [
                'name' => trans('order-action.installed_by'),
            ],
        ];

        $project->orderActions()->createMany(array_map(function($orderAction, $order) {
            return array_merge($orderAction, ['order' => $order + 1]);
        }, $orderActions, array_keys($orderActions)));
    }

    /**
     * Add default roles to project
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    protected function addRoles(Project $project)
    {
        $roles = [
            [
                'name' => trans('role.admin'),
                'permissions' => ['all']
            ],
            [
                'name' => trans('role.administrative'),
                'permissions' => [
                    'all.prospect.add', 
                    'all.prospect.update', 
                    'all.prospect.delete', 
                    'all.prospect.export', 
                    'all.prospect.sms', 
                    'all.prospect.interaction', 
                    'all.prospect.order', 
                    'all.prospect.event'
                ]
            ],
            [
                'name' => trans('role.commercial'),
                'permissions' => [
                    'all.prospect.add', 
                    'all.prospect.update', 
                    'all.prospect.delete', 
                    'all.prospect.export', 
                    'all.prospect.sms', 
                    'all.prospect.interaction', 
                    'all.prospect.order', 
                    'all.prospect.event'
                ]
            ],
            [
                'name' => trans('role.accountant'),
                'permissions' => [
                    'all.prospect.add', 
                    'all.prospect.update', 
                    'all.prospect.delete', 
                    'all.prospect.export', 
                    'all.prospect.sms', 
                    'all.prospect.interaction', 
                    'all.prospect.order', 
                    'all.prospect.event'
                ]
            ],
            [
                'name' => trans('role.confirmator'),
                'permissions' => [
                    'all.prospect.add', 
                    'all.prospect.update', 
                    'all.prospect.delete', 
                    'all.prospect.export', 
                    'all.prospect.sms', 
                    'all.prospect.interaction', 
                    'all.prospect.order', 
                    'all.prospect.event'
                ]
            ],
            [
                'name' => trans('role.telepro'),
                'permissions' => [
                    'all.prospect.add', 
                    'all.prospect.update', 
                    'all.prospect.delete', 
                    'all.prospect.export', 
                    'all.prospect.sms', 
                    'all.prospect.interaction', 
                    'all.prospect.order', 
                    'all.prospect.event'
                ]
            ],
        ];

        foreach ($roles as $role) {
            $createdRole = $project->roles()->create([
                'name' => $role['name'],
                'guard_name' => "web",
            ]);

            foreach ($role['permissions'] as $permission) {
                if (!($p = Permission::where('name', $permission)->first())) {
                    $p = Permission::create([
                        'name' => $permission, 
                        'guard_name' => "web"
                    ]);

                    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
                }

                $createdRole->givePermissionTo($p);
            }
        }
    }

    /**
     * Project job category default configuration
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    protected function jobCategoryConfiguration(Project $project)
    {
        // Check project job category
        if (!$project->job_category) {
            return false;
        }
        
        // Check project job category is valid
        if (!($config = config("project.job_category." . $project->job_category, null))) {
            return false;
        }

        // Create folders
        $folders = [];
        foreach (data_get($config, "folders", []) as $name) {
            $folders[] = ['name' => $name];
        }
        $project->folders()->createMany($folders);

        // Create threads
        $threads = [];
        foreach (data_get($config, "threads", []) as $name => $private) {
            $threads[] = ['name' => $name, 'private' => $private];
        }
        $project->threads()->createMany($threads);

        // Create fields
        $fields = [];
        foreach (data_get($config, "fields", []) as $name) {
            $fields[] = ['name' => $name, 'meta' => true];
        }
        $project->fields()->createMany($fields);

        // Create calendars
        $calendars = [];
        foreach (data_get($config, "calendars", []) as $name => $type) {
            $calendars[] = ['name' => $name, 'type' => $type];
        }
        $project->calendars()->createMany($calendars);

        // Create categories
        foreach (data_get($config, "categories", []) as $name => $labelNames) {
            $category = $project->categories()->create(['name' => $name]);

            $labels = [];
            foreach ($labelNames as $name) {
                $labels[] = ['name' => $name];
            }
            $category->labels()->createMany($labels);
        }
        
        // Create roles
        foreach (data_get($config, "roles", []) as $name => $permissions) {
            $createdRole = $project->roles()->create([
                'name' => $name,
                'guard_name' => "web",
            ]);

            foreach ($permissions as $permission) {
                if (!($p = Permission::where('name', $permission)->first())) {
                    $p = Permission::create([
                        'name' => $permission, 
                        'guard_name' => "web"
                    ]);

                    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
                }

                $createdRole->givePermissionTo($p);
            }
        }
        
        // Create message templates
        $messageTemplates = [];
        foreach (data_get($config, "message_templates", []) as $name => $body) {
            $messageTemplates[] = ['name' => $name, 'body' => $body];
        }
        $project->messageTemplates()->createMany($messageTemplates);

        return true;
    }
}