<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Filters\Filters;
use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Lab404\Impersonate\Services\ImpersonateManager;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable // implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, Impersonate, Notifiable;
    

    /**
     * The "booted" method of the model.
     */
    /*protected static function booted(): void
    {
        static::addGlobalScope(new UserScope());
    }*/
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'phone_number',
        'mobile_phone_number',
        'street',
        'street_bis',
        'postal_code',
        'city',
        'country',
        
        'ip_postal_code',
        'ip_city',
        'ip_country',
        'ip_state',
        'ip_latitude',
        'ip_longitude',

        'state',
        'county',
        'ip_address',
        'latitude',
        'longitude',
        'role',
        'default_projects',
        'two_factors_secret',
        'two_factors_check_type',
        'last_activity',
        'last_project_id',
        'creator_id',
        'banned_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factors_secret',
        'two_factors_check_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_activity' => 'datetime',
        'email_verified_at' => 'datetime',
        'default_projects' => 'json',
    ];


    // Attributes

    /**
     * Get is super admin
     *
     * @return string
     */
    public function getIsSuperAdminAttribute()
    {
        // return true;
        return $this->role == 'super_admin';
    }
    

    /**
     * Get is project admin
     *
     * @return string
     */
    public function getIsProjectAdminAttribute()
    {
        return request()->project && (request()->project->creator_id == $this->id || $this->can('', request()->project));
    }

    /**
     * Determine whether the user can be assigned a prospect.
     *
     * @return bool
     */
    public function getIsAssignableForProspectAttribute(): bool
    {
        if (empty($this->role)) {
            return true;
        }

        $role = strtolower(trim($this->role));
        $excludedRoles = ['super_admin', 'administrateur', 'superviseur', 'administrator', 'supervisor'];

        return !in_array($role, $excludedRoles, true);
    }

    /**
     * Get is super admin
     *
     * @return string
     */
    public function getTwoFactorsActivatedAttribute()
    {
        return !empty($this->two_factors_secret);
    }
    

    // Builder

    /**
     * Apply all relevant filters
     *
     * @param Builder $query
     * @param Filters $filters
     * @return Builder
     */
    public function scopeFilter(Builder $builder, Filters $filters)
    {
        return $filters->apply($builder);
    }

    

    // Relationships
    
    /**
     * Affected events
     */
    public function affectedEvents()
    {
        return $this->hasMany(Event::class, "user_id");
    }

    /**
     * Users
     */
    public function assignableUsers()
    {
        return $this->belongsToMany(User::class, 'user_assignable_user', 'user_id', 'assignable_user_id')->withPivot(['project_id']);
    }
    
    /**
     * Assigning users
     */
    public function assigningUsers()
    {
        return $this->belongsToMany(User::class, 'user_assignable_user', 'assignable_user_id', 'user_id');
    }

    /**
     * Assigning roles
     */
    public function assigningRoles()
    {
        return $this->belongsToMany(Role::class, 'role_assignable_user', 'assignable_user_id', 'role_id');
    }
    
    /**
     * Calendars
     */
    public function calendars()
    {
        return $this->belongsToMany(Calendar::class, 'user_calendar');
    }

    /**
     * Categories
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'user_category');
    }

    /**
     * Documents
     */
    public function documents()
    {
        return $this->belongsToMany(Document::class, 'user_document');
    }

    /**
     * Events
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, "user_event");
    }

    /**
     * Exports
     */
    public function exports()
    {
        return $this->hasMany(Export::class, "creator_id");
    }

    /**
     * Files
     */
    public function files()
    {
        return $this->hasMany(UserFile::class);
    }

    /**
     * Folders
     */
    public function folders()
    {
        return $this->belongsToMany(Folder::class, 'user_folder');
    }

    /**
     * Groups
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'user_group');
    }

    /**
     * Imports
     */
    public function imports()
    {
        return $this->hasMany(Import::class, "creator_id");
    }

    /**
     * Labels
     */
    public function labels()
    {
        return $this->belongsToMany(Label::class, 'user_label');
    }

    /**
     * Last project
     */
    public function lastProject()
    {
        return $this->belongsTo(Project::class, 'last_project_id');
    }

    /**
     * Menus
     */
    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'user_menu');
    }

    /**
     * Messages
     */
    public function messages()
    {
        return $this->belongsToMany(Message::class, 'user_message');
    }

    /**
     * Order Actions
     */
    public function orderActions()
    {
        return $this->belongsToMany(OrderAction::class, 'user_order_action', 'user_id', 'action_id');
    }

    /**
     * Order Steps
     */
    public function orderSteps()
    {
        return $this->belongsToMany(OrderStep::class, 'user_order_step', 'user_id', 'step_id');
    }

    /**
     * Products
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'user_product');
    }

    /**
     * Projects
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'user_project')->withPivot('order');
    }

    /**
     * Prospects
     */
    public function prospects()
    {
        return $this->belongsToMany(Prospect::class, 'prospect_user');
    }
    
    /**
     * Questionnaires
     */
    public function questionnaires()
    {
        return $this->belongsToMany(Questionnaire::class, 'user_questionnaire');
    }
    
    /**
     * Threads
     */
    public function threads()
    {
        return $this->belongsToMany(Thread::class, 'user_thread');
    }
    
    /**
     * Vehicles
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    
    // Custom methods

    /**
     * 
     */
    public function impersonate(User $user, $guardName = 'web')
    {
        return app(ImpersonateManager::class)->take($this, $user, $guardName);
    }


    // Scope

    public function scopeForCurrentUser($query) {
    	if (!auth()->user() || auth()->user()->is_super_admin || !getPermissionsTeamId()) {
            return;
    	}
    
        // Hide super admin
        // from admin
        // that is not affected to the admin
    	if (auth()->user()->is_project_admin) {
            $query->where(function($query) {
                $query
                    ->whereNull('role')
                    ->orWhere('role', '!=', 'super_admin')
                    ->orWhere('id', auth()->id())
                    ->orWhereHas('assigningUsers', function($query) {
                        $query->where('id', auth()->id());
                    });
            });
            return;
    	}
    
        $query->where(function($query) {
            $query
                ->whereHas('assigningUsers', function($query) {
                    $query->where('id', auth()->id());
                })
                ->orWhere('id', auth()->id())
                /*->orWhereHas('assigningRoles', function($query) {
                    $query->whereIn('id', auth()->user()->roles->pluck('id'));
                })*/;
        });
    }
}
