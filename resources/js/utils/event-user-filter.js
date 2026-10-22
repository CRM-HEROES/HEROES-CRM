// Keep the same owner-or-participant semantics as the API's withUsers filter.
export function filterEventsByUsers(events = [], params = {}) {
    if (!Array.isArray(params?.withUsers)) return events;
    if (params.withUsers.length === 0) return [];
    const selected = new Set(params.withUsers.map(String));
    return events.filter((event) => selected.has(String(event.user_id ?? event.user?.id)) ||
        event.users?.some((user) => selected.has(String(user.id))));
}
