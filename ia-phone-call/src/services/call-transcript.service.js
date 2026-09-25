const DEFAULT_SUMMARY_MODEL = 'models/gemini-2.5-flash';

export function createCallTranscript({ callId = null, phoneNumber = null } = {}) {
    const turns = [];

    function normalizeText(text) {
        return String(text ?? '').replace(/\s+/g, ' ').trim();
    }

    function append(role, text, metadata = {}) {
        const value = normalizeText(text);
        if (!value) return null;

        const entry = {
            role,
            text: value,
            timestamp: new Date().toISOString(),
            ...metadata
        };

        turns.push(entry);
        return entry;
    }

    function appendAi(text, metadata = {}) {
        return append('ai', text, metadata);
    }

    function appendUser(text, metadata = {}) {
        return append('user', text, metadata);
    }

    function appendSystem(text, metadata = {}) {
        return append('system', text, metadata);
    }

    function toJSON() {
        return {
            callId,
            phoneNumber,
            turns: turns.map((turn) => ({ ...turn }))
        };
    }

    function toText() {
        return turns
            .map((turn) => {
                const label = turn.role === 'ai' ? 'IA' : turn.role === 'user' ? 'Utilisateur' : turn.role === 'system' ? 'Système' : turn.role.toUpperCase();
                return `[${label}] ${turn.text}`;
            })
            .join('\n');
    }

    function hasContent() {
        return turns.length > 0;
    }

    function getSummaryPrompt() {
        const transcriptText = toText();

        return `Tu es un assistant CRM. Résume cette conversation de façon claire et exploitable pour le service client.

Règles :
- Réponds en français.
- Produis un résumé court mais utile.
- Donne : 1) le contexte de l'appel, 2) les besoins du client, 3) les informations importantes, 4) le statut final, 5) la prochaine action recommandée.
- Si les informations sont incomplètes, indique "inconnu" au lieu d'inventer.

Conversation :
${transcriptText || 'Aucune conversation enregistrée.'}`;
    }

    return {
        append,
        appendAi,
        appendUser,
        appendSystem,
        toJSON,
        toText,
        hasContent,
        getSummaryPrompt,
        turns
    };
}

export async function summarizeCallTranscript({ transcript, apiKey, model = DEFAULT_SUMMARY_MODEL }) {
    if (!apiKey) {
        throw new Error('Gemini API key manquante pour générer le résumé.');
    }

    const text = typeof transcript === 'string' ? transcript : transcript.toText();
    const prompt = `Tu es un assistant CRM. Résume cette conversation de manière concise et exploitable.

Règles :
- Réponds en français.
- Structure le résumé en 5 points : contexte, besoin, infos clés, statut, prochaine action.
- Si une information manque, mets "inconnu".

Conversation :
${text || 'Aucune conversation enregistrée.'}`;

    const response = await fetch(`https://generativelanguage.googleapis.com/v1beta/${model}:generateContent?key=${apiKey}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            contents: [{
                role: 'user',
                parts: [{ text: prompt }]
            }]
        })
    });

    if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`Échec de génération du résumé Gemini: ${response.status} ${errorText}`);
    }

    const data = await response.json();
    const summary = data?.candidates?.[0]?.content?.parts
        ?.map((part) => part.text)
        .join('\n')
        ?.trim();

    if (!summary) {
        throw new Error('Aucun résumé retourné par Gemini.');
    }

    return summary;
}

export async function sendCallSummaryToLaravel({
    summary,
    transcript,
    prospectId = null,
    callUuid = null,
    agentId = null,
    callerNumber = null,
    destinationNumber = null,
    projectSlug = null,
    analysis = {},
    laravelBaseUrl = process.env.LARAVEL_BASE_URL,
    secret = process.env.AI_PHONE_AGENT_SHARED_SECRET,
} = {}) {
    if (!laravelBaseUrl) {
        console.warn('[Summary] LARAVEL_BASE_URL non configuré: aucun résumé envoyé au CRM.');
        return null;
    }

    const endpoint = `${String(laravelBaseUrl).replace(/\/+$/, '')}/api/webhooks/ai-phone-agent/calls`;
    const payload = {
        prospect_id: prospectId,
        call_uuid: callUuid,
        agent_id: agentId,
        caller_number: callerNumber,
        destination_number: destinationNumber,
        project_slug: projectSlug,
        summary: typeof summary === 'string' ? summary.trim() : null,
        transcript: typeof transcript === 'string'
            ? transcript
            : typeof transcript?.toText === 'function'
                ? transcript.toText()
                : '',
        analysis: {
            ...(analysis && typeof analysis === 'object' ? analysis : {}),
            summary: typeof summary === 'string' ? summary.trim() : null,
        },
        test_mode: false,
    };

    if (!payload.prospect_id && !payload.caller_number) {
        console.warn('[Summary] Impossible d’envoyer le résumé sans prospect_id ni caller_number.');
        return null;
    }

    const response = await fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            ...(secret ? { 'X-AI-Agent-Secret': secret } : {}),
        },
        body: JSON.stringify(payload),
    });

    const text = await response.text();
    let result = null;
    try {
        result = text ? JSON.parse(text) : null;
    } catch (error) {
        result = { raw: text };
    }

    if (!response.ok) {
        console.warn('[Summary] Échec envoi résumé au CRM:', response.status, text);
        return { ok: false, status: response.status, payload: result };
    }

    console.log('[Summary] Résumé envoyé au CRM avec succès:', result);
    return { ok: true, status: response.status, payload: result };
}
