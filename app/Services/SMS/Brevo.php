<?php

namespace App\Services\SMS;

use Illuminate\Support\Facades\Http;

class Brevo
{
    protected $apiUri;
    protected $config;

    // TYPE

    // Message transactionnel (par défaut). Aucune restriction horaire.
    public const TYPE_TRANSACTIONAL = "transactional";

    // Message marketing. Soumis aux restrictions horaires et doit
    // contenir un moyen de désinscription ([STOP CODE]).
    public const TYPE_MARKETING = "marketing";

    /**
     *
     */
    protected function setup()
    {
        $this->config = [];

        // Brevo API

        $this->apiUri = config('brevo.api_uri', 'https://api.brevo.com/v3/transactionalSMS/send');

        // Brevo API KEY
        $this->config['apikey'] = config('brevo.api_key');

        // Sender ID (nom ou numéro affiché comme émetteur)
        // Max 15 chiffres ou 11 caractères alphanumériques
        $this->config['sender'] = config('brevo.sender');

        $this->config['type'] = self::TYPE_TRANSACTIONAL;
    }

    /**
     * Definir le type de sms à envoyer
     *
     * Valeurs autorisées :
     * Brevo::TYPE_TRANSACTIONAL (valeur par défaut)
     * Brevo::TYPE_MARKETING
     */
    public function setType($type)
    {
        if (
            !in_array($type, [
                self::TYPE_TRANSACTIONAL,
                self::TYPE_MARKETING,
            ])
        ) {
            throw new \Exception(trans('sms.brevo.error.invalid_type'));
        }

        $this->config['type'] = $type;
    }

    /**
     * Definir l'émetteur du SMS
     *
     * @param sender
     *        Le nom ou le numéro de l'émetteur du SMS
     *        (Maximum 15 chiffres ou 11 caractères alphanumériques).
     */
    public function setSender($sender)
    {
        $this->config['sender'] = $sender;
    }

    /**
     * Normaliser le numéro de téléphone au format international
     * attendu par Brevo (sans "+", sans espaces).
     */
    protected function normalizeNumber($dest)
    {
        return preg_replace('/[^0-9]/', '', $dest);
    }

    /**
     * Envoyer un message
     *
     * @param dest
     *        numéro mobile du destinataire
     *
     * @param message
     *        message à envoyer
     */
    public function sendSMS($dest, $message)
    {
        $this->setup();

        // Vérifier que l'API est bien configurée
        if (empty($this->config['apikey']) || empty($this->config['sender'])) {
            throw new \Exception(trans('sms.brevo.error.empty_setting'));
        }

        // Vérifier que le message n'est pas vide
        if (empty($message)) {
            throw new \Exception(trans('sms.brevo.error.empty_message'));
        }

        // Vérifier que le destinataire est bien renseigné
        if (empty($dest)) {
            throw new \Exception(trans('sms.brevo.error.empty_number'));
        }

        $response = Http::withHeaders([
            'api-key' => $this->config['apikey'],
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post($this->apiUri, [
            'sender' => $this->config['sender'],
            'recipient' => $this->normalizeNumber($dest),
            'content' => $message,
            'type' => $this->config['type'],
        ]);

        // CHECK RESPONSE

        if ($response->successful()) {
            return $response->json('messageId', true);
        }

        $error = $response->json('message', $response->body());

        throw new \Exception($error . " : " . trans('sms.brevo.error.other'));
    }
}