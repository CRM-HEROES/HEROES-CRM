<?php

namespace App\Jobs\Import\ImportColumnToField;

use App\Jobs\Import\ImportColumnToFieldInterface;

class UserField implements ImportColumnToFieldInterface
{
    protected $userRepository;
    
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * 
     */
    public function handle(&$prospect, $field, $value)
    {
        try {
            $data = json_decode($value, true);

            if (!is_array($data)) {
                return;
            }

            $prospect['users'] = array_map(function($user) {
                return $this->userRepository->findOrCreateUser($user)->id;
            }, $data);
        } catch (\Exception $e) {}
    }
}