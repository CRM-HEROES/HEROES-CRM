const PALETTE = ["#7939b8", "#c2185b", "#00897b", "#6d4c41", "#4527a0", "#ad1457", "#00695c", "#558b2f"];

export function duplicateColor(groupId) {
    return PALETTE[Math.abs(Number(groupId) || 0) % PALETTE.length];
}
