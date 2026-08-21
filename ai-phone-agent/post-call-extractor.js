"use strict";
const config = require("./config");
const schema = {
    type: "object", properties: {
        besoin_exprime: { type: ["string", "null"] }, budget: { type: ["string", "null"] }, disponibilite: { type: ["string", "null"] },
        niveau_interet: { type: ["string", "null"], enum: ["chaud", "tiede", "froid", null] }, objections: { type: "array", items: { type: "string" } },
        action_suivante: { type: ["string", "null"] }, resume_appel: { type: "string" }, statut_appel: { type: "string", enum: ["interesse", "pas_interesse", "rappeler", "injoignable", "erreur"] },
    }, required: ["besoin_exprime", "budget", "disponibilite", "niveau_interet", "objections", "action_suivante", "resume_appel", "statut_appel"], additionalProperties: false,
};
const instruction = "Extrait uniquement les informations explicitement dites dans ce transcript. Si une information n'a pas été explicitement mentionnée, mets null. N'invente, ne déduis et n'infère jamais une valeur. Retourne le JSON conforme au schéma demandé.";
function validate(value) {
    for (const key of schema.required) if (!(key in value) || value[key] === undefined) throw new Error(`Extraction missing ${key}`);
    if (!Array.isArray(value.objections) || !schema.properties.statut_appel.enum.includes(value.statut_appel)) throw new Error("Invalid extraction JSON");
    return value;
}
async function extractTranscript(transcript, request = global.fetch) {
    if (!config.gemini.apiKey) return null;
    const url = `https://generativelanguage.googleapis.com/v1beta/models/${config.gemini.extractionModel}:generateContent?key=${encodeURIComponent(config.gemini.apiKey)}`;
    const response = await request(url, { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ systemInstruction: { parts: [{ text: instruction }] }, contents: [{ role: "user", parts: [{ text: transcript }] }], generationConfig: { responseMimeType: "application/json", responseJsonSchema: schema } }) });
    if (!response.ok) throw new Error(`Gemini extraction failed (${response.status}).`);
    const payload = await response.json();
    return validate(JSON.parse(payload.candidates?.[0]?.content?.parts?.[0]?.text || "{}"));
}
module.exports = { extractTranscript, validate, schema };
