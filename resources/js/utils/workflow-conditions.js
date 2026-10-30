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

    if (type.includes(".filter_")) {
        const first = filtersFor(catalog, subject)[0];

        if (first) {
            config.op = "and";
            config.items = [newFilterItem(catalog, subject, first.name)];
        }
    }

    return config;
}
