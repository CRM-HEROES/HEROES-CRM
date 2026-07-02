<?php

namespace App\Http\Controllers\API\Project;

use App\Events\ProjectUserAttached;
use App\Filters\UserRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Models\UserSetting;
use App\Utils\ProjectSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    protected $fields = [
        'name',
        'last_name',
        'email',
        'street',
        'street_bis',
        'postal_code',
        'city',
        'country',
        'last_activity',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Project $project, UserRequestFilters $userRequestFilters)
    {
        return $this->getUsers(
            $request, 
            $userRequestFilters, 
            $project
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, User $user)
    {
        abort_unless(auth()->user()->can('', $project), 404);
        abort_unless($project->users()->find($user->id), 404);

        $user->load([
            'calendars' => function($query) use($project) {
                $query->where('project_id', $project->id)->select('id', 'name', 'color', 'bgcolor');
            },
            'categories' => function($query) use($project) {
                $query->where('project_id', $project->id)->select('id', 'name', 'color', 'bgcolor');
            },
            'documents' => function($query) use($project) {
                $query->where('project_id', $project->id)->select('id', 'project_id', 'name', 'for');
            },
            'exports' => function($query) use($project) {
                $query->where('project_id', $project->id)->select('id', 'created_at', 'creator_id');
            },
            'folders' => function($query) use($project) {
                $query->where('project_id', $project->id)->select('id', 'name', 'color', 'bgcolor', 'for');
            },
            'groups' => function($query) use($project) {
                $query->where('project_id', $project->id)->select('id', 'name', 'color', 'bgcolor');
            },
            'imports' => function($query) use($project) {
                $query->where('project_id', $project->id)->where('project_id', $project->id);
            },
            'menus' => function($query) use($project) {
                $query->where('project_id', $project->id)->select('id', 'name', 'color', 'bgcolor', 'filters');
            },
            'orderActions' => function($query) use($project) {
                $query->where('project_id', $project->id)->select('id', 'name', 'color', 'bgcolor');
            },
            'orderSteps' => function($query) use($project) {
                $query->where('project_id', $project->id)->select('id', 'name', 'color', 'bgcolor');
            },
            'questionnaires' => function($query) use($project) {
                $query->where('project_id', $project->id)->select('id', 'name');
            },
            'threads' => function($query) use($project) {
                $query->where('project_id', $project->id)->select('id', 'name', 'color', 'bgcolor');
            },
            'assignableUsers' => function($query) use($project) {
                $query->wherePivot('project_id', $project->id)->select('id', 'name');
            },
            'roles' => function($query) use($project) {
                $query->wherePivot('project_id', $project->id)->select('id', 'name');
            },
            'vehicles' => function($query) use($project) {
                $query->where('project_id', $project->id)->select('id', 'user_id', 'name');
            },
        ]);
        
        return $user;
    }

    /**
     * Display the specified resource.
     */
    public function store(Request $request, Project $project)
    {
        // abort_unless(auth()->user()->can('projectUserAdd', $project), 404);
        abort_unless(auth()->user()->is_super_admin || auth()->user()->can('', $project), 404);

        $this->validate($request, [
            'email' => 'required',
        ]);

        abort_unless($user = User::where('email', $request->input('email'))->first(), 404, trans('project.error.unknown_user'));
        abort_if($project->users()->find($user->id), 400, trans('project.error.user_already_attached'));

        $project
            ->users()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$user->id => [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]]);

        ProjectUserAttached::dispatch($project, $user);

        return $user;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, User $user)
    {
        abort_unless(auth()->user()->can('projectUserUpdate', $project), 404);
        abort_unless(!$user->is_super_admin || auth()->user()->id == $user->id, 400);

        $defaultFieldValues = $request->only('color', 'bgcolor');

        $metaFieldValues = collect($request->meta)->only(
            $project->fields()
                ->where('for', "user")
                ->get()
                ->pluck('slug')
                ->toArray()
        )->toArray();

        $projectUser = $project->users()->find($user->id);
        $meta = $projectUser && $projectUser->pivot->meta ? $projectUser->pivot->meta : [];

        $project->users()->syncWithoutDetaching([$user->id => array_merge(
            $defaultFieldValues,
            [
                'creator_id' => auth()->id(),
                'meta' => array_merge($meta, $metaFieldValues)
            ],
        )]);

        $user->update($request->only([
            'name',
            'last_name',
            'email',
            'phone_number',
            'mobile_phone_number',
            'street',
            'street_bis',
            'postal_code',
            'city',
            'country',
            'role',
            'default_projects',
        ]));

        if ($request->filled('password')) {
            $user->password = bcrypt(request('password'));
            $user->save();
        }

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, User $user)
    {
        /// abort_unless(auth()->user()->can('projectUserDelete', $project), 404);
        abort_unless(auth()->user()->is_super_admin || auth()->user()->can('', $project), 404);
        
        $project->users()->detach([$user->id]);

        return ['message' => trans('common.success.detached_resource')];
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        $this->validate($request, [
            'users' => 'required',
            'action' => 'required',
        ]);

        // Only users
        // associated to the current project
        $users = $project
            ->users()
            ->whereIn('id', $request->input('users'));
            // ->withTrashed();

        switch ($request->input('action')) {
            case "delete":
                $project->users()->detach($users->pluck('id')->toArray());
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
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
            'key' => 'users-table',
        ])->first();

        if ($userSetting) {
            return array_map(function($field) {
                return $field['key'];
            }, $userSetting->value);
        }

        // From project settings
        $projectSetting = ProjectSetting::get($project, 'users-table');

        if ($projectSetting) {
            return $projectSetting->map(function($field) {
                return $field['key'];
            })->toArray();
        }

        // From project settings
        $defaultSetting = config('default-settings.users-table', null);

        if ($defaultSetting) {
            return collect($defaultSetting)->map(function($field) {
                return $field['key'];
            })->toArray();
        }

        // Default
        return array_merge(
            $project
                ->fields()
                ->where('for', "user")
                ->where('meta', false)
                ->get()
                ->pluck('slug')
                ->toArray(), [
                    'events', 'users'
                ]
        );
    }

    /**
     * Get events
     */
    protected function getUsers(Request $request, UserRequestFilters $filters, Project $project)
    {
        // Count
        $count = min($request->input('count', 30), 500);

        // Sort By
        $sortBy = $request->input('sortBy', "id");
        if (!in_array($sortBy, $this->fields)) {
            $sortBy = "name";
        }

        // Sort Order
        $sortOrder = $request->input('sortOrder', "") == "desc" ? "desc" : "asc";

        // Fields
        $fields = $this->getFields(
            $request, 
            $project
        );

        // Fields to select
        $defaultFields = array_merge(
            $this->fields, ['id']
        );

        // Categories in which we select labels associated to prospects
        $categories = array_map(function($field) {
            return Str::replace('category->', '', $field);
        }, array_filter($fields, function($field) {
            return Str::startsWith($field, 'category->');
        }));
        
        // Result
        return $project
            ->users()
            ->forCurrentUser()
            ->select($defaultFields)
            ->when(in_array('calendars', $fields), function($query) use($project) {
                $query->with(['calendars' => function ($query) use($project) {
                    $query
                        ->select('id', 'name', 'color', 'bgcolor')
                        ->where('project_id', $project->id);
                }]);
            })
            ->when(in_array('categories', $fields), function($query) use($project) {
                $query->with(['categories' => function ($query) use($project) {
                    $query
                        ->select('id', 'name', 'color', 'bgcolor')
                        ->where('project_id', $project->id);
                }]);
            })
            ->when(in_array('documents', $fields), function($query) use($project) {
                $query->with(['documents' => function ($query) use($project) {
                    $query
                        ->select('id', 'name')
                        ->where('project_id', $project->id);
                }]);
            })
            ->when(in_array('folders', $fields), function($query) use($project) {
                $query->with(['folders' => function ($query) use($project) {
                    $query
                        ->select('id', 'name', 'color', 'bgcolor')
                        ->where('project_id', $project->id);
                }]);
            })
            ->when(in_array('groups', $fields), function($query) use($project) {
                $query->with(['groups' => function ($query) use($project) {
                    $query
                        ->select('id', 'name', 'color', 'bgcolor')
                        ->where('project_id', $project->id);
                }]);
            })
            ->when(!empty($categories), function($query) use($categories) {
                $query->with(['labels' => function($query) use($categories) {
                    $query
                        ->select('id', 'name', 'color', 'bgcolor', 'category_id')
                        ->whereIn('category_id', $categories);
                }]);
            })
            ->when(in_array('menus', $fields), function($query) use($project) {
                $query->with(['menus' => function ($query) use($project) {
                    $query
                        ->select('id', 'name', 'color', 'bgcolor')
                        ->where('project_id', $project->id);
                }]);
            })
            ->when(in_array('order_actions', $fields), function($query) use($project) {
                $query->with(['orderActions' => function ($query) use($project) {
                    $query
                        ->select('id', 'name', 'color', 'bgcolor')
                        ->where('project_id', $project->id);
                }]);
            })
            ->when(in_array('order_steps', $fields), function($query) use($project) {
                $query->with(['orderSteps' => function ($query) use($project) {
                    $query
                        ->select('id', 'name', 'color', 'bgcolor')
                        ->where('project_id', $project->id);
                }]);
            })
            ->when(in_array('questionnaires', $fields), function($query) use($project) {
                $query->with(['questionnaires' => function ($query) use($project) {
                    $query
                        ->select('id', 'name')
                        ->where('project_id', $project->id);
                }]);
            })
            ->when(in_array('roles', $fields), function($query) use($project) {
                $query->with(['roles' => function ($query) use($project) {
                    $query
                        ->select('id', 'name')
                        ->where('model_has_roles.project_id', $project->id);
                }]);
            })
            ->when(in_array('threads', $fields), function($query) use($project) {
                $query->with(['threads' => function ($query) use($project) {
                    $query
                        ->select('id', 'name', 'color', 'bgcolor')
                        ->where('project_id', $project->id);
                }]);
            })
            ->when(in_array('users', $fields), function($query) use($project) {
                $query->with(['assignableUsers' => function ($query) use($project) {
                    $query
                        ->select('id', 'name')
                        ->wherePivot('project_id', $project->id);
                }]);
            })
            ->filter($filters)
            ->orderBy($sortBy, $sortOrder)
            ->paginate($count)
            ->map(function($data) use($fields) {
                if ($data->pivot->meta) {
                    $meta = array_filter($data->pivot->meta, function($key) use($fields) {
                        return in_array("meta->$key", $fields);
                    }, ARRAY_FILTER_USE_KEY);

                    if (empty($meta)) {
                        unset($data->pivot->meta);
                    } else {
                        $data->pivot->meta = $meta;
                    }
                }

                return $data;
            });
    }
}
