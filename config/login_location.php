<?php

return [
    // true : une connexion depuis un nouveau pays est refusée tant que l'utilisateur
    // n'a pas cliqué sur "C'était moi" dans l'email.
    // false : la connexion est acceptée, l'email est seulement envoyé (ancien comportement).
    'block' => env('LOGIN_LOCATION_BLOCK', true),

    // Durée de validité des liens de l'email (en minutes). Passé ce délai, les liens
    // ne marchent plus : l'utilisateur se reconnecte et reçoit un nouvel email.
    'link_ttl_minutes' => (int) env('LOGIN_LOCATION_LINK_TTL', 60),
];
