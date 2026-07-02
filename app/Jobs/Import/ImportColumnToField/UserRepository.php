<?php

namespace App\Jobs\Import\ImportColumnToField;

use App\Jobs\Import\ImportColumnToFieldInterface;
use App\Models\Import;
use App\Models\Project;
use App\Models\User;

class UserRepository
{
    protected $users;
    protected Project $project;
    
    public function __construct(Project $project)
    {
        $this->project = $project;
        $this->users = $this->project->users;
    }

    /**
     * Find or create user inside the import project
     */
    public function findOrCreateUser($user)
    {
        if (!($foundUser = $this->users->where('email', $user['email'])->first())) {
            if (!($foundUser = User::where('email', $user['email'])->first())) {
                $foundUser = User::create(array_merge($user, ['password' => '']));
            }

            $this->project->users()->syncWithoutDetaching($foundUser->id);
            $this->users->push($foundUser);
        }

        return $foundUser;
    }
}