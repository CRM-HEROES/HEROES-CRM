export default [
    {
        value: "kavkom",
        label: "Kavkom",
        logo: "/images/partenaire-ext/kavkom.png",
        fields: [
            { key: "api_token", label: "Jeton API (X-API-TOKEN)", type: "text" },
            { key: "domain_uuid", label: "Identifiant du domaine (Domain UUID)", type: "text" },
            { key: "phone_number", label: "Numéro sortant (DID autorisé)", type: "tel" },
            { key: "extension", label: "Extension (poste)", type: "text" },
        ],
    },
    {
        value: "cloudtalk",
        label: "CloudTalk",
        logo: "/images/partenaire-ext/cloudtalk.png",
        fields: [
            { key: "api_key_id", label: "Identifiant de la clé API (API Key ID)", type: "text" },
            { key: "api_key_secret", label: "Secret de la clé API (API Key Secret)", type: "text" },
            { key: "agent_id", label: "Agent", type: "select" },
        ],
    },
    {
        value: "ringover",
        label: "Ringover",
        logo: "/images/partenaire-ext/ringover.png",
        fields: [{ key: "api_token", label: "Jeton API (Token)", type: "text" }],
    },
    {
        value: "twilio",
        label: "Twilio",
        logo: "/images/partenaire-ext/twilio.ico",
        fields: [
            { key: "account_sid", label: "SID du compte (Account SID)", type: "text" },
            { key: "auth_token", label: "Jeton d'authentification (Auth Token)", type: "text" },
            { key: "api_key_sid", label: "SID de la clé API (API Key SID)", type: "text" },
            { key: "api_key_secret", label: "Secret de la clé API (API Key Secret)", type: "text" },
            { key: "twiml_app_sid", label: "SID de l'application TwiML", type: "text" },
            { key: "caller_id_number", label: "Numéro appelant (Caller ID)", type: "tel" },
        ],
    },
];
