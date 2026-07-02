<?php

return [

    'success' => [
        'added_user' => "User added successfully.",
    ],

    'type' => [
        'professional' => [
            'name' => "Professional",
            'description' => "Professional project",
        ],
        'personal' => [
            'name' => "Personal",
            'description' => "Personal project in which you can manage your own prospects",
        ],
        'demo' => [
            'name' => "Demo",
            'description' => "Demo project - List of Silicon Valley bosses",
        ],
    ],

    'error' => [
        'unauthorized_user' => "You are not authorized to access this project, please contact the project administrator to gain access.",
        'unknown_user' => "Unknown user.",
        'user_already_attached' => "User already attched to the project.",
        'unknown_prospect' => "Unknown prospect.",
        'unknown_role' => "Unknown role.",
    ],

    'fields' => [
        'capital' => "Capital",
        'email' => "Email",
        'name' => "Name",
        'naf' => "NAF",
        'siret' => "SIRET",
        'phone_number' => "Phone number",
        'mobile_phone_number' => "Mobile phone number",
        'street' => "Street",
        'street_bis' => "Street 2",
        'city' => "City",
        'postal_code' => "Postal code",
        'country' => "Country",
    ],
];