<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    
    // Relationships
    
    /**
     * Users
     */
    public function assignableUsers()
    {
        return $this->belongsToMany(User::class, 'role_assignable_user', 'role_id', 'assignable_user_id');
    }
    
    /**
     * Categories
     */
    public function calendars()
    {
        return $this->belongsToMany(Calendar::class, 'role_calendar');
    }

    /**
     * Categories
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'role_category');
    }

    /**
     * Documents
     */
    public function documents()
    {
        return $this->belongsToMany(Document::class, 'role_document');
    }

    /**
     * Folders
     */
    public function folders()
    {
        return $this->belongsToMany(Folder::class, 'role_folder');
    }

    /**
     * Groups
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'role_group');
    }

    /**
     * Labels
     */
    public function labels()
    {
        return $this->belongsToMany(Label::class, 'role_label');
    }

    /**
     * Order Actions
     */
    public function orderActions()
    {
        return $this->belongsToMany(OrderAction::class, 'role_order_action', 'role_id', 'action_id');
    }

    /**
     * Order Steps
     */
    public function orderSteps()
    {
        return $this->belongsToMany(OrderStep::class, 'role_order_step', 'role_id', 'step_id');
    }

    /**
     * Products
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'user_product');
    }

    /**
     * Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Questionnaires
     */
    public function questionnaires()
    {
        return $this->belongsToMany(Questionnaire::class, 'role_questionnaire');
    }
    
    /**
     * Threads
     */
    public function threads()
    {
        return $this->belongsToMany(Thread::class, 'role_thread');
    }
    
}
