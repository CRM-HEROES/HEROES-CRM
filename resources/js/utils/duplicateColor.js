/**
 * One color per duplicate cluster (every prospect sharing the same
 * duplicate_group_id gets the same color), so distinct groups of
 * duplicates are visually told apart in the table instead of all
 * blending into a single "duplicate" color. Picked away from the hues
 * already used for deleted/processed/selected/viewed rows (red/orange/
 * blue/yellow).
 */
const PALETTE = [
    "#7939b8", // violet
    "#c2185b", // magenta
    "#00897b", // teal
    "#6d4c41", // brown
    "#4527a0", // indigo
    "#ad1457", // dark pink
    "#00695c", // dark teal
    "#558b2f", // olive
];

export function duplicateColor(groupId) {
    return PALETTE[groupId % PALETTE.length];
}
