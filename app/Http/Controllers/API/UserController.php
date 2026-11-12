<?php

namespace App\Http\Controllers\API;

use App\Filters\UserRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
     * L'utilisateur reçoit une invitation (lien), jamais un mot de passe.
     */
    public function store(Request $request)
    {
        // abort_unless(auth()->user()->is_super_admin, 404);

        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'status' => [
                'nullable',
                Rule::in(['super_admin']),
            ],
            'role' => [
                'nullable',
                Rule::in(['super_admin']),
            ],
        ]);

        // Seul un super admin peut créer un super admin (avant : n'importe quel appelant pouvait envoyer "role")
        $wantsSuperAdmin = $request->input('role') === 'super_admin' || $request->input('status') === 'super_admin';
        abort_if($wantsSuperAdmin && !auth()->user()->is_super_admin, 403, 'Seul un super admin peut créer un super admin.');

        abort_if(User::where('email', $request->input('email'))->first(), 400, 'User already exists!');

        // Liste blanche : ni role, ni mot de passe, ni 2FA, ni bannissement ne viennent de la requête
        $data = $request->only(array_diff($this->fields, ['role', 'last_activity']));

        return DB::transaction(function () use ($data, $wantsSuperAdmin) {
            $user = new User(array_merge($data, [
                'creator_id' => auth()->id(),
                'password' => Hash::make(bin2hex(random_bytes(32))),
            ]));

            if ($wantsSuperAdmin) {
                $user->role = 'super_admin';
            }

            $user->must_change_password = true;
            $user->save();

            if (!\App\Services\UserInvitation::send($user)) {
                throw new HttpResponseException(response()->json([
                    'message' => 'Connexion Internet requise : l’email d’invitation n’a pas pu être envoyé. L’utilisateur n’a pas été créé.',
                ], 503));
            }

            return $user;
        });
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        abort_unless(auth()->user()->is_super_admin || auth()->id() == $user->id || $user->creator_id == auth()->id(), 404, 'You cannot update this user!');

        // Le mot de passe ne se modifie JAMAIS ici : l'utilisateur le change lui-même via un email de confirmation
        // (POST /password/email). Toute valeur "password" envoyée est ignorée.
        $data = $request->only($this->fields);

        // Le rôle n'est modifiable que par un super admin (avant : chacun pouvait se donner le rôle super_admin)
        if (!auth()->user()->is_super_admin) {
            unset($data['role']);
        }

        $user->update($data);

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Renvoie l'invitation d'un compte qui n'a jamais été activé.
     */
    public function resendInvitation(User $user)
    {
        abort_unless(auth()->user()->is_super_admin || $user->creator_id == auth()->id(), 404);

        // Jamais sur un compte actif : cela écraserait son mot de passe et le déconnecterait
        if (!$user->must_change_password) {
            return response()->json([
                'success' => false,
                'message' => 'Ce compte est déjà activé : une invitation ne peut être renvoyée qu’à un compte en attente.',
            ], 422);
        }

        $sent = \App\Services\UserInvitation::send($user);

        return response()->json([
            'success' => $sent,
            'message' => $sent
                ? 'Une nouvelle invitation a été envoyée à l’utilisateur.'
                : 'L’invitation n’a pas pu être envoyée.',
        ], $sent ? 200 : 503);
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
