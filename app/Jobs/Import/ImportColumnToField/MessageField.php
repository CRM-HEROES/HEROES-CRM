<?php

namespace App\Jobs\Import\ImportColumnToField;

use App\Jobs\Import\ImportColumnToFieldInterface;

class MessageField implements ImportColumnToFieldInterface
{
    protected $date;
    protected $userRepository;
    
    public function __construct(UserRepository $userRepository)
    {
        $this->date = \Carbon\Carbon::now()->format('Y-m-d h:i:s');
        $this->userRepository = $userRepository;
    }

    /**
     * 
     */
    public function handle(&$prospect, $field, $value)
    {
        try {
            $messages = json_decode($value, true);

            foreach ($messages as $message) {
                $messageUser = $message['user'] ? 
                    $this->userRepository->findOrCreateUser($message['user']) : 
                    null;

                $prospect['messages'][] = [
                    'body' => data_get($message, 'body'),
                    'plain_text' => $this->htmlToText(data_get($message, 'body', "")),
                    'from_user' => $messageUser ? 1 : 0,
                    'creator_id' => $messageUser ? $messageUser->id : null,
                    'thread_id' => $field->id,
                    'created_at' => data_get($message, 'created_at', $this->date),
                ];
            }
        } catch (\Exception $e) {}
    }
    
    /**
     * 
     */
    protected function htmlToText($html) {
        // Remove HTML tags
        $text = strip_tags($html);
    
        // Convert HTML entities to plain text
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
        // Normalize line breaks
        $text = preg_replace('/\R+/u', "\n", $text);
    
        // Trim extra whitespace
        $text = trim($text);

        $text = strtolower($text);
    
        return $text;
    }
}