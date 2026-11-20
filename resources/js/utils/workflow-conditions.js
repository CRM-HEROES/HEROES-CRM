/**
 * Starting values for the condition editor and for the triggers.
 * Pure functions: shared by the components, tested without Vue.
 */

// What a new criterion starts with, by type of value
const DEFAULT_VALUE = { boolean: true, number: 1, users: [], groups: [], labels: [] };

function clone(value) {
    return JSON.parse(JSON.stringify(value));
}

/**
 * The criteria offered for a subject (prospect, order, appointment)
 */
export function filtersFor(catalog, subject) {
    return (catalog.conditions.filters && catalog.conditions.filters[subject]) || [];
}

/**
 * A new criterion { filters: { name: value } }, null if the catalog has no such criterion
 */
export function newFilterItem(catalog, subject, name) {
    const filter = filtersFor(catalog, subject).find((f) => f.name === name);

    if (!filter) {
        return null;
    }

    return { filters: { [name]: clone(DEFAULT_VALUE[filter.type] ?? null) } };
}

/**
 * A new date / time check
 */
export function newCheckItem(name) {
    return name === "work_hours"
        ? { check: name, days: [1, 2, 3, 4, 5], from: "08:00", to: "17:00" }
        : { check: name, from: null, to: null };
}

// What a test on a field of the prospect can say (see ConditionEvaluator::FIELD_TESTS)
export const FIELD_TESTS = ["is", "is_not", "filled", "empty"];

/**
 * The test of a "field" item: is, is_not, filled or empty
 */
export function fieldTest(item) {
    if ("is" in item) return "is";
    if ("is_not" in item) return "is_not";

    return item.filled === false ? "empty" : "filled";
}

/**
 * A "field" item for a field, a test and a value: { field, is | is_not | filled }
 */
export function newFieldItem(slug, test = "is", value = "") {
    switch (test) {
        case "is_not":
            return { field: slug, is_not: value };
        case "filled":
            return { field: slug, filled: true };
        case "empty":
            return { field: slug, filled: false };
        default:
            return { field: slug, is: value };
    }
}

/**
 * The value a "field" item compares to ("" for filled / empty)
 */
export function fieldValue(item) {
    return "is" in item ? item.is : "is_not" in item ? item.is_not : "";
}

/**
 * The fields offered for a test: the custom fields of the project, and the one that is computed
 *
 * @param {Array} fields the fields of the project ({ slug, name, meta })
 * @returns {Array<{slug: string, name: string, computed?: boolean}>}
 */
export function fieldChoices(fields) {
    return [
        ...(fields || []).filter((f) => f.meta && f.for === "prospect").map((f) => ({ slug: f.slug, name: f.name || f.slug })),
        { slug: "plus_50", name: "plus_50", computed: true },
    ];
}

/**
 * The configuration of a trigger just chosen.
 *
 * A trigger that watches a filter starts with its first criterion already in
 * place, so that there is something to choose from and to read instead of an
 * empty box.
 */
export function triggerConfig(catalog, type) {
    const subject = type.split(".")[0];
    const config = { trigger: type };

    // A schedule starts with values that are valid: the 8th of every month at 11:00, over an hour
    if (type === "prospect.scheduled") {
        return { ...config, days: [8], at: "11:00", window_minutes: 60 };
    }

    if (type.includes(".filter_")) {
        const first = filtersFor(catalog, subject)[0];

        if (first) {
            config.op = "and";
            config.items = [newFilterItem(catalog, subject, first.name)];
        }
    }

    return config;
}
