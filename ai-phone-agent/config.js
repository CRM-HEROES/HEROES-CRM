require("dotenv").config();

function required(name, fallback = undefined) {
    const value = process.env[name] ?? fallback;
    return value;
}

module.exports = {
    httpPort: Number(process.env.HTTP_PORT || 4000),
    wsPort: Number(process.env.WS_PORT || 4001),
    transcriptWsPort: Number(process.env.TRANSCRIPT_WS_PORT || 4002),
    wsPublicUrl: required("WS_PUBLIC_URL", "ws://127.0.0.1:4001/"),

    sharedSecret: required("AI_PHONE_AGENT_SHARED_SECRET", ""),
    laravelBaseUrl: (required("LARAVEL_BASE_URL", "http://127.0.0.1:8000") || "").replace(/\/+$/, ""),
    testMode: process.env.TEST_MODE === "true",
    testAllowedNumbers: (process.env.TEST_ALLOWED_NUMBERS || "").split(",").map((value) => value.replace(/\D/g, "")).filter(Boolean),
    recordingDir: required("CALL_RECORDING_DIR", "/data/calls"),
    localTestSipUser: required("LOCAL_TEST_SIP_USER", "1000"),

    gemini: {
        apiKey: required("GEMINI_API_KEY", ""),
        model: required("GEMINI_LIVE_MODEL", "models/gemini-2.5-flash-native-audio-preview-09-2025"),
        apiVersion: required("GEMINI_API_VERSION", "v1alpha"),
        extractionModel: required("GEMINI_EXTRACTION_MODEL", "gemini-2.5-flash-lite"),
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
    kavkom: {
        callsWsEnabled: process.env.KAVKOM_CALLS_WS_ENABLED !== "false",
        liveBaseUrl: required("KAVKOM_LIVE_BASE_URL", "https://live.kavkom.com"),
        refreshAccessToken: required("KAVKOM_REFRESH_ACCESS_TOKEN", ""),
        refreshTokenEndpoint: required("KAVKOM_REFRESH_TOKEN_ENDPOINT", ""),
        refreshBearer: required("KAVKOM_REFRESH_BEARER", ""),
        userUuid: required("KAVKOM_USER_UUID", ""),
        // Kavkom's acquire-access endpoint expects JSON numbers, e.g.
        // { extensions: [501] }, not string values such as ["501"].
        extensions: (process.env.KAVKOM_WS_EXTENSIONS || process.env.KAVKOM_EXTENSION || "")
            .split(",")
            .map((value) => Number.parseInt(value.trim(), 10))
            .filter(Number.isInteger),
        originateUrl: required("KAVKOM_ORIGINATE_URL", ""),
    },
    orchestrator: {
        httpPort: Number(process.env.ORCHESTRATOR_HTTP_PORT || 4010),
        bridgeUrl: (required("VOICE_BRIDGE_URL", "http://127.0.0.1:4000") || "").replace(/\/+$/, ""),
    },
};
