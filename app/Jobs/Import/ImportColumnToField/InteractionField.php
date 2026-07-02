<?php

namespace App\Jobs\Import\ImportColumnToField;

use App\Jobs\Import\ImportColumnToFieldInterface;

class InteractionField implements ImportColumnToFieldInterface
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

            $prospect['interactions'] = array_map(function($interaction) {
                $interactionUser = $interaction['user'] ? 
                    $this->userRepository->findOrCreateUser($interaction['user']) : 
                    null;
                
                return [
                    'source'     => data_get($interaction, 'source', ""),
                    'created_at' => data_get($interaction, 'created_at', null),
                    'creator_id' => $interactionUser ? $interactionUser->id : null,
                ];
            }, $data);
        } catch (\Exception $e) {}
    }
}