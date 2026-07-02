<?php

return [

    'smsbox' => [

        'error' => [
            '01' => "Des paramètres sont manquants. Vérifiez les arguments de votre requête.",
            '02' => "Identifiants incorrects, compte banni ou restriction par adresse IP.\nVérifiez vos identifiants ou rendez-vous à la rubrique « Sécurité API » de votre Espace Client.",
            '03' => "Crédit épuisé ou insuffisant. Rechargez votre compte en achetant des Crédits SMS.",
            '04' => "Numéro de destination invalide ou mal formaté.\nVérifiez si le numéro de votre destinataire est correctement saisi.",
            '05' => "Erreur d'exécution interne à notre application.\nRéessayez ultérieurement.",
            'other' => "L'envoi a échoué pour une autre raison (numéro présent en liste noire, opérateur indisponible, indicatif non géré, ...).",
    
            'invalid_encoding' => "Coding invalide, veuillez renseigner un coding parmi les propositions suivantes:\nSMSBOX::MODE_STANDARD,\nSMSBOX::MODE_EXPERT,\nSMSBOX::MODE_RESPONSE.",
            'invalid_charset' => "Charset invalide, veuillez renseigner un charset parmi les propositions suivantes:\nSMSBOX::CHARSET_ISO_8859_1,\nSMSBOX::CHARSET_ISO_8859_15,\nSMSBOX::CHARSET_UTF_8.",
            'invalid_mode' => "Mode invalide, veuillez renseigner un mode parmi les propositions suivantes:\nSMSBOX::MODE_STANDARD,\nSMSBOX::MODE_EXPERT,\nSMSBOX::MODE_RESPONSE.",
            'invalid_strategy' => "Strategy invalide, veuillez renseigner un strategy parmi les propositions suivantes:\nSMSBOX::STRATEGY_PRIVATE,\nSMSBOX::STRATEGY_ALERT,\nSMSBOX::STRATEGY_GROUP,\nSMSBOX::STRATEGY_COMMERCIAL.",
            'invalid_type' => "Type invalide, veuillez renseigner un type parmi les propositions suivantes:\nSMSBOX::TYPE_CLASSIC,\nSMSBOX::TYPE_FLASH.",
            
            'origin' => "L'origine ne peut être définie que si l'envoi est configuré en mode Expert.",
            'empty_setting' => "Veuillez configurer votre API SMSBOX.",
            'empty_message' => "Le message à envoyer est vide.",
            'empty_number' => "Aucun numéro mobile n\'a été renseigné.",
            'max_numbers' => "Le nombre de numéro à vérifier devrait être inférieur ou égal à 100.",
        ],

    ],

    'ultramsg' => [
            
        'error' => [
            'empty_setting' => "Aucun instance/token ultramsg n'est configuré.",
            'empty_response' => "Aucune réponse reçue.",
        ],

    ],

    'mtarget' => [
            
        'error' => [
            'empty_setting' => "Aucun login username/password n'est configuré.",
            'empty_response' => "Aucune réponse reçue.",
        ],

    ],

    'error' => [
        'unknown_source' => "Source de message \":source\" inconnue"
    ],

];