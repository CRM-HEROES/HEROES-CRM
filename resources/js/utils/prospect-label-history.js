export function addedLabelNames(revision, labels = []) {
    const before = revision.old_value || "";
    const after = revision.new_value || "";
    if (!after) return [];
    // Preserved legacy assignments contain a single historical label name.
    if (revision.old_value === null) return [after];

    const oldNames = knownSelection(before, labels);
    const newNames = knownSelection(after, labels);
    if (oldNames && newNames) {
        return newNames.filter((name) => !oldNames.includes(name));
    }

    // A renamed/deleted label may be missing from current category metadata.
    // Prove an addition using the complete stored name before using metadata.
    const added = labels.filter((label) => recordsLabelAddition(revision, label.name))
        .map((label) => label.name);
    if (added.length) return added;
    if (!before) return [after];

    const oldParts = before.split(", ");
    const newParts = after.split(", ");
    while (oldParts.length && oldParts[0] === newParts[0]) {
        oldParts.shift();
        newParts.shift();
    }
    while (oldParts.length && oldParts[oldParts.length - 1] === newParts[newParts.length - 1]) {
        oldParts.pop();
        newParts.pop();
    }
    if (oldParts.length || !newParts.length) return [];
    const inserted = newParts.join(", ");
    return knownSelection(inserted, labels) || [inserted];
}

function knownSelection(value, labels) {
    const names = labels.map((label) => label.name).filter(Boolean)
        .sort((left, right) => right.length - left.length);
    const selection = [];
    let remaining = value;
    while (remaining) {
        const name = names.find((name) =>
            remaining === name || remaining.startsWith(name + ", ")
        );
        if (!name) return null;
        selection.push(name);
        remaining = remaining.slice(name.length + 2);
    }
    return selection;
}

export function assignmentRecorded(item, revisions) {
    const assignment = item.pivot || item;
    const author = assignment.creator_id ?? null;
    const date = new Date(assignment.created_at || item.created_at).getTime();
    return revisions.some((revision) =>
        revision.key === "category->" + item.category_id &&
        new Date(revision.created_at).getTime() === date &&
        String(revision.user_id ?? revision.user?.id ?? "") === String(author ?? "") &&
        recordsLabelAddition(revision, item.name)
    );
}

function recordsLabelAddition(revision, name) {
    const before = revision.old_value || "";
    const after = revision.new_value || "";
    if ((", " + before + ", ").includes(", " + name + ", ")) return false;
    if (after === name) return true;
    // Removing the added full name must recover the previous selection exactly.
    // This is also safe for names containing commas or SQL wildcard characters.
    return (after.startsWith(name + ", ") && after.slice(name.length + 2) === before) ||
        (after.endsWith(", " + name) && after.slice(0, -name.length - 2) === before) ||
        (after.replace(", " + name + ", ", ", ") === before && after !== before);
}
