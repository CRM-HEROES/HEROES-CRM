import store from "@/store";
import {
    FETCH_PROSPECTS,
    SET_PROSPECTS_PAGE,
    SET_PROSPECT_PARAMS,
} from "@/actions/project/prospect";

const sameId = (left, right) => String(left) === String(right);

/**
 * Select or clear the visible values from a with/without filter pair.
 */
export function setProspectFilterSelection(
    items,
    checked,
    withKey,
    withoutKey,
    getValue = (item) => item.id
) {
    const values = items.map(getValue).reduce((unique, value) => {
        if (!unique.some((existing) => sameId(existing, value))) {
            unique.push(value);
        }

        return unique;
    }, []);
    const params = { ...(store.state.project.prospectsParams || {}) };

    [withKey, withoutKey].forEach((key) => {
        const current = Array.isArray(params[key]) ? params[key] : [];
        const remaining = current.filter(
            (value) => !values.some((selected) => sameId(selected, value))
        );

        if (remaining.length) {
            params[key] = remaining;
        } else {
            delete params[key];
        }
    });

    if (checked && values.length) {
        const included = params[withKey] || [];
        params[withKey] = [
            ...included,
            ...values.filter((value) =>
                included.every((existing) => !sameId(existing, value))
            ),
        ];
    }

    store.commit(SET_PROSPECT_PARAMS, params);
    store.commit(SET_PROSPECTS_PAGE, 1);
    store.dispatch(FETCH_PROSPECTS);
}

/**
 * Merge a visible list into an array-backed filter without changing hidden selections.
 */
export function mergeVisibleSelection(
    selected,
    items,
    checked,
    getValue = (item) => item.id
) {
    const current = Array.isArray(selected) ? selected : [];
    const visible = items.map(getValue).reduce((unique, value) => {
        if (!unique.some((existing) => sameId(existing, value))) {
            unique.push(value);
        }

        return unique;
    }, []);
    const remaining = current.filter(
        (value) => !visible.some((candidate) => sameId(candidate, value))
    );

    if (!checked) {
        return remaining;
    }

    return [
        ...remaining,
        ...visible.filter(
            (value) => !remaining.some((existing) => sameId(existing, value))
        ),
    ];
}
