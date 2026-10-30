/**
 * Translation key of a workflow identifier.
 *
 * The identifiers of the catalog contain dots and hyphens ("prospect.created",
 * "prospect-attach-user"): vue-i18n reads a dot as a path, so the translations
 * of these identifiers are keyed with underscores.
 */
export function slug(id) {
    return String(id).replace(/[.-]/g, "_");
}
