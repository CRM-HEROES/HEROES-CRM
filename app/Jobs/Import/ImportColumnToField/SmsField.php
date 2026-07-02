<?php

namespace App\Jobs\Import\ImportColumnToField;

use App\Jobs\Import\ImportColumnToFieldInterface;

class SmsField implements ImportColumnToFieldInterface
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

            $prospect['sms'] = array_map(function($sms) {
                $smsUser = $sms['user'] ? $this->userRepository->findOrCreateUser($sms['user']) : null;
                
                return [
                    'message'    => data_get($sms, 'message'),
                    'source'     => data_get($sms, 'source'),
                    'created_at' => data_get($sms, 'created_at'),
                    'sent_at'    => data_get($sms, 'sent_at'),
                    'creator_id' => $smsUser ? $smsUser->id : null,
                ];
            }, $data);
        } catch (\Exception $e) {}
    }
}