import { agendaEventScope, projectEventRange, globalEventRange } from "@/utils/agenda-event-range-cache";
import { ADD_EVENT, UPDATE_EVENT, REMOVE_EVENT } from "@/actions/project/event";
import { ADD_PROSPECT_EVENT, UPDATE_PROSPECT_EVENT, REMOVE_PROSPECT_EVENT } from "@/actions/project/prospect/event";
import { ADD_CALENDAR, UPDATE_CALENDAR, REMOVE_CALENDAR } from "@/actions/project/calendar";

const edits = new Set([
    ADD_EVENT, UPDATE_EVENT, REMOVE_EVENT,
    ADD_PROSPECT_EVENT, UPDATE_PROSPECT_EVENT, REMOVE_PROSPECT_EVENT,
    ADD_CALENDAR, UPDATE_CALENDAR, REMOVE_CALENDAR,
]);

export default function agendaEventRangeCache(store) {
    let scope = agendaEventScope(store.state);
    const writes = new WeakMap();
    store.subscribe((mutation, state) => {
        const next = agendaEventScope(state);
        if (next !== scope || mutation.type === "RESET") {
            scope = next;
            projectEventRange.clear();
            globalEventRange.clear();
        } else if (edits.has(mutation.type)) {
            projectEventRange.invalidate();
            globalEventRange.invalidate();
        }
    });

    function finish(action) {
        const release = writes.get(action);
        if (!release) return;
        writes.delete(action);
        release();
    }

    store.subscribeAction({
        before(action) {
            if (!edits.has(action.type)) return;
            const projectDone = projectEventRange.beginWrite();
            const globalDone = globalEventRange.beginWrite();
            writes.set(action, () => { projectDone(); globalDone(); });
        },
        after: finish,
        error: finish,
    });
}
