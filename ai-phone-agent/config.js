require("dotenv").config();

function required(name, fallback = undefined) {
    const value = process.env[name] ?? fallback;
    return value;
}

module.exports = {
    httpPort: Number(process.env.HTTP_PORT || 4000),
    wsPort: Number(process.env.WS_PORT || 4001),
    wsPublicUrl: required("WS_PUBLIC_URL", "ws://127.0.0.1:4001/"),

    sharedSecret: required("AI_PHONE_AGENT_SHARED_SECRET", ""),
    laravelBaseUrl: (required("LARAVEL_BASE_URL", "http://127.0.0.1:8000") || "").replace(/\/+$/, ""),

    gemini: {
        apiKey: required("GEMINI_API_KEY", ""),
        model: required("GEMINI_LIVE_MODEL", "models/gemini-2.5-flash-native-audio-preview-09-2025"),
        apiVersion: required("GEMINI_API_VERSION", "v1alpha"),
    },

    freeswitch: {
        eslHost: required("FS_ESL_HOST", "127.0.0.1"),
        eslPort: Number(process.env.FS_ESL_PORT || 8021),
        eslPassword: required("FS_ESL_PASSWORD", "ClueCon"),
        dialPrefixInternal: required("FS_DIAL_PREFIX_INTERNAL", "sofia/gateway/kavkom/"),
        dialPrefixExternal: required("FS_DIAL_PREFIX_EXTERNAL", "sofia/gateway/kavkom/"),
        conferenceProfile: required("FS_CONFERENCE_PROFILE", "ai-agent"),
        loopbackTarget: required("FS_LOOPBACK_TARGET", "loopback/ai-agent-tap/default"),
        callerIdNumber: required("FS_CALLER_ID_NUMBER", ""),
        callerIdName: required("FS_CALLER_ID_NAME", "Heroes CRM"),
    },
};
