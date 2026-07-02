<?php

return [

    'smsbox' => [

        'error' => [
            '01' => "Some parameters are missing. Please check your request arguments.",
            '02' => "Incorrect credentials, banned account, or IP address restriction.\nPlease check your credentials or visit the 'API Security' section in your Client Area.",
            '03' => "Insufficient or depleted credit. Please recharge your account by purchasing SMS credits.",
            '04' => "Invalid or improperly formatted destination number.\nPlease check if the recipient's number is correctly entered.",
            '05' => "Internal execution error in our application.\nPlease try again later.",
            'other' => "Sending failed for another reason (number blacklisted, operator unavailable, unsupported prefix, ...).",

            'invalid_encoding' => "Invalid coding. Please provide one of the following coding options:\nSMSBOX::MODE_STANDARD,\nSMSBOX::MODE_EXPERT,\nSMSBOX::MODE_RESPONSE.",
            'invalid_charset' => "Invalid charset. Please provide one of the following charset options:\nSMSBOX::CHARSET_ISO_8859_1,\nSMSBOX::CHARSET_ISO_8859_15,\nSMSBOX::CHARSET_UTF_8.",
            'invalid_mode' => "Invalid mode. Please provide one of the following mode options:\nSMSBOX::MODE_STANDARD,\nSMSBOX::MODE_EXPERT,\nSMSBOX::MODE_RESPONSE.",
            'invalid_strategy' => "Invalid strategy. Please provide one of the following strategy options:\nSMSBOX::STRATEGY_PRIVATE,\nSMSBOX::STRATEGY_ALERT,\nSMSBOX::STRATEGY_GROUP,\nSMSBOX::STRATEGY_COMMERCIAL.",
            'invalid_type' => "Invalid type. Please provide one of the following type options:\nSMSBOX::TYPE_CLASSIC,\nSMSBOX::TYPE_FLASH.",
            
            'origin' => "The origin can only be set if the sending is configured in Expert mode.",
            'empty_setting' => "Please configure your SMSBOX API.",
            'empty_message' => "The message to be sent is empty.",
            'empty_number' => "No mobile number has been provided.",
            'max_numbers' => "The number of numbers to be checked should be less than or equal to 100.",
        ],

    ],

    'ultramsg' => [
            
        'error' => [
            'empty_setting' => "No ultramsg instance/token is configured.",
            'empty_response' => "No response received.",
        ],

    ],

    'mtarget' => [
            
        'error' => [
            'empty_setting' => "No MTarget username/password is configured.",
            'empty_response' => "No response received.",
        ],

    ],

    'error' => [
        'unknown_source' => "Unkown SMS source \":source\""
    ],

];