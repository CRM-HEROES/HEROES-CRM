export default [
    {
        value: "kavkom",
        label: "Kavkom",
        fields: [
            { key: "api_token", label: "Jeton API (X-API-TOKEN)", type: "password" },
            { key: "domain_uuid", label: "Domain UUID", type: "text" },
            { key: "phone_number", label: "Numéro sortant (DID autorisé)", type: "tel" },
            { key: "extension", label: "Extension", type: "text" },
        ],
    },
    {
        value: "ringover",
        label: "Ringover",
        fields: [{ key: "api_token", label: "Token", type: "text" }],
    },
    {
        value: "twilio",
        label: "Twilio",
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
