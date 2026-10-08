export default [
    {
        value: "kavkom",
        label: "Kavkom",
        logo: "/images/partenaire-ext/kavkom.png",
        fields: [
            { key: "api_token", label: "Jeton API (X-API-TOKEN)", type: "password" },
            { key: "domain_uuid", label: "Domain UUID", type: "text" },
            { key: "phone_number", label: "Numéro sortant (DID autorisé)", type: "tel" },
            { key: "extension", label: "Extension", type: "text" },
        ],
    },
    {
        value: "cloudtalk",
        label: "CloudTalk",
        logo: "/images/partenaire-ext/cloudtalk.png",
        fields: [
            { key: "api_key_id", label: "API Access Key ID", type: "text" },
            { key: "api_key_secret", label: "API Access Key Secret", type: "password" },
            { key: "agent_id", label: "Agent", type: "select" },
        ],
    },
    {
        value: "ringover",
        label: "Ringover",
        logo: "/images/partenaire-ext/ringover.png",
        fields: [{ key: "api_token", label: "Token", type: "text" }],
    },
    {
        value: "twilio",
        label: "Twilio",
        logo: "/images/partenaire-ext/twilio.ico",
        fields: [
            { key: "account_sid", label: "Account SID", type: "text" },
            { key: "auth_token", label: "Auth Token", type: "password" },
            { key: "api_key_sid", label: "API Key SID", type: "text" },
            { key: "api_key_secret", label: "API Key Secret", type: "password" },
            { key: "twiml_app_sid", label: "TwiML App SID", type: "text" },
            { key: "caller_id_number", label: "Numéro appelant", type: "tel" },
        ],
    },
];
