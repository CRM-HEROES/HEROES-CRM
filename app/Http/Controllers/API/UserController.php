<?php

namespace App\Http\Controllers\API;

use App\Filters\UserRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    protected $fields = [
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
        
        'ip_postal_code',
        'ip_city',
        'ip_country',
        'ip_state',
        'ip_latitude',
        'ip_longitude',
        'ip_address',
        
        'last_activity',
        'default_projects',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, UserRequestFilters $userRequestFilters)
    {
        abort_unless(auth()->user()->is_super_admin, 404);

        return $this->getUsers(
            $request, 
            $userRequestFilters
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        abort_unless(auth()->user()->is_super_admin || auth()->id() == $user->id, 404);

        $user->load([
            'projects' => function($query) {
                // $query->select('id', 'name', 'slug');
            },
            'calendars' => function($query) {
                $query->select('id', 'name', 'color', 'bgcolor', 'project_id');
            },
            'calendars.project' => function($query) {
                $query->select('id', 'name');
            },
            'categories' => function($query) {
                $query->select('id', 'name', 'color', 'bgcolor', 'project_id');
            },
            'categories.project' => function($query) {
                $query->select('id', 'name');
            },
            'documents' => function($query) {
                $query->select('id', 'project_id', 'name', 'for', 'project_id');
            },
            'documents.project' => function($query) {
                $query->select('id', 'name', 'slug');
            },
            'folders' => function($query) {
                $query->select('id', 'name', 'color', 'bgcolor', 'project_id');
            },
            'folders.project' => function($query) {
                $query->select('id', 'name');
            },
            'groups' => function($query) {
                $query->select('id', 'name', 'color', 'bgcolor', 'project_id');
            },
            'groups.project' => function($query) {
                $query->select('id', 'name');
            },
            'threads' => function($query) {
                $query->select('id', 'name', 'color', 'bgcolor', 'project_id');
            },
            'threads.project' => function($query) {
                $query->select('id', 'name');
            },
            'roles' => function($query) {
                // $query->wherePivot('project_id', $project->id)->select('id', 'name');
            },
        ]);
        
        return $user;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // abort_unless(auth()->user()->is_super_admin, 404);

        $this->validate($request, [
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
            'status' => [
                'nullable',
                Rule::in(['super_admin']),
            ]
        ]);

        abort_if(User::where('email', $request->input('email'))->first(), 400, 'User already exists!');

        return User::create(array_merge($request->only($this->fields), [
            'creator_id' => auth()->id(),
            'password' => bcrypt($request->input('password'))
        ]));
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        abort_unless(auth()->user()->is_super_admin || auth()->id() == $user->id || $user->creator_id == auth()->id(), 404, 'You cannot update this user!');

        $user->update($request->only(
            $this->fields
        ));

        if ($request->filled('password')) {
            $user->password = bcrypt(request('password'));
            $user->save();
        }

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Get fields
     */
    protected function getFields(Request $request)
    {
        // From params
        if ($request->has('fields')) {
            return explode(',', $request->input('fields'));
        }

        $settingKey = 'global-users-table';

        // From user settings
        $userSetting = UserSetting::where([
            'project_id' => null,
            'user_id' => auth()->id(),
            'key' => $settingKey,
        ])->first();

        if ($userSetting) {
            $setting = collect($userSetting->value);
        } else {
            // Default
            $setting = collect(config('default-settings.' . $settingKey, []));
        }

        return $setting->map(function($field) {
            return $field['key'];
        })->toArray();
        // From project settings
        /*$defaultSetting = config('default-settings.users-table', null);

        if ($defaultSetting) {
            return collect($defaultSetting)->map(function($field) {
                return $field['key'];
            })->toArray();
        }*/

    }

    /**
     * Get events
     */
    protected function getUsers(Request $request, UserRequestFilters $filters)
    {
        // Count
        $count = min($request->input('count', 30), 500);

        // Sort By
        $sortBy = $request->input('sortBy', "id");
        if (!in_array($sortBy, $this->fields)) {
            $sortBy = "id";
        }

        // Sort Order
        $sortOrder = $request->input('sortOrder', "") == "desc" ? "desc" : "asc";

        // Fields
        $fields = $this->getFields(
            $request
        );
        /*$fields = array_map(function($field) {
            return $field['key'];
        }, $this->getFields(
            $request
        ));*/

        // Fields to select
        $defaultFields = array_merge(
            $this->fields, ['id', 'last_project_id']
        );

        // Result
        return User::select($defaultFields)
            ->when(in_array('projects', $fields), function($query) {
                $query->with(['projects' => function ($query) {
                    // $query->select('id', 'name', 'slug');
                }]);
            })
            ->when(in_array('last-project', $fields), function($query) {
                $query->with(['lastProject' => function ($query) {
                    $query->select('id', 'name', 'slug');
                }]);
            })
            ->filter($filters)
            ->orderBy($sortBy, $sortOrder)
            ->paginate($count);
    }
}
