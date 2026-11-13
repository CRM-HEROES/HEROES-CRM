<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Webklex\PHPIMAP\ClientManager;

$cm = new ClientManager();

$client = $cm->make([
    'host'          => 'imap.mail.ovh.net',
    'port'          => 993,
    'encryption'    => 'ssl',
    'validate_cert' => true,
    'username'      => 'sophie@careline.doctor',
    'password'      => '......',
    'protocol'      => 'imap',
]);

try {
    echo "Connexion en cours...\n";

    $client->connect();

    echo "Connexion réussie !\n";

    $folder = $client->getFolder('INBOX');

    $message = $folder->messages()
        ->all()
        ->limit(1)
        ->get()
        ->first();

    if ($message) {
        echo "\nExpéditeur : " . $message->getFrom()[0]->mail;
        echo "\nSujet : " . $message->getSubject();
        echo "\nDate : " . $message->getDate();
        // echo "\nContenu HTML :\n" . $message->getHTMLBody();
        echo "\nContenu texte :\n" . $message->getTextBody();
    } else {
        echo "Aucun message trouvé.\n";
    }

    $client->disconnect();

} catch (\Throwable $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
limite recuperation email pour eviter la recupration de tous emails.