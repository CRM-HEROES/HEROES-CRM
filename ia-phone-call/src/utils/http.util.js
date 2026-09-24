/** Taille maximale acceptée pour le corps d'une requête JSON (1 Mo). */
const MAX_BODY_BYTES = 1024 * 1024;

/** Erreur HTTP portant le code de statut à renvoyer au client. */
export class HttpError extends Error {
    /**
     * @param {number} statusCode
     * @param {string} message
     */
    constructor(statusCode, message) {
        super(message);
        this.name = 'HttpError';
        this.statusCode = statusCode;
    }
}

/**
 * Écrit une réponse JSON et termine la requête.
 *
 * @param {import('http').ServerResponse} res
 * @param {number} statusCode
 * @param {unknown} payload
 */
export function sendJson(res, statusCode, payload) {
    res.writeHead(statusCode);
    res.end(JSON.stringify(payload));
}

/**
 * Lit et parse le corps JSON d'une requête HTTP.
 *
 * @param {import('http').IncomingMessage} req
 * @returns {Promise<Object>} Objet parsé (vide si le corps est vide)
 */
export function readJsonBody(req) {
    return new Promise((resolve, reject) => {
        let body = '';
        let size = 0;

        req.on('data', (chunk) => {
            size += chunk.length;
            if (size > MAX_BODY_BYTES) {
                reject(new HttpError(413, 'Corps de requête trop volumineux'));
                req.destroy();
                return;
            }
            body += chunk;
        });

        req.on('end', () => {
            if (!body.trim()) return resolve({});
            try {
                resolve(JSON.parse(body));
            } catch {
                reject(new HttpError(400, 'JSON invalide'));
            }
        });

        req.on('error', reject);
    });
}
