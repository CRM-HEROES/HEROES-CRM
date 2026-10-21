import cache, { dailyDirectionCacheScope, dailyDirectionEventsSignature } from "@/utils/event-daily-direction-cache";
import {
    ADD_EVENT, UPDATE_EVENT, REMOVE_EVENT, SET_EVENTS,
    INVALIDATE_EVENT_USER_DAILY_DIRECTIONS,
} from "@/actions/project/event";
import { ADD_PROSPECT_EVENT, UPDATE_PROSPECT_EVENT, REMOVE_PROSPECT_EVENT } from "@/actions/project/prospect/event";
import { SET_GLOBAL_EVENTS } from "@/actions/event";

const edits = new Set([ADD_EVENT, UPDATE_EVENT, REMOVE_EVENT, ADD_PROSPECT_EVENT, UPDATE_PROSPECT_EVENT, REMOVE_PROSPECT_EVENT]);
const routeFields = ["user_id", "calendar_id", "started_at", "ended_at", "location", "latitude", "longitude"];
const affectsRoute = ({ type, payload }) => edits.has(type) &&
    (![UPDATE_EVENT, UPDATE_PROSPECT_EVENT].includes(type) || routeFields.some((field) => Object.prototype.hasOwnProperty.call(payload, field)));

function snapshot(events = []) {
    return new Map(events.filter(Boolean).map((event) => [String(event.id), {
        id: event.id,
        user_id: event.user_id,
        calendar_id: event.calendar_id,
        calendar: { type: event.calendar?.type },
        started_at: event.started_at,
        ended_at: event.ended_at,
        location: event.location,
        latitude: event.latitude,
        longitude: event.longitude,
    }]));
}

export default function eventDailyDirectionCache(store) {
    let scope = dailyDirectionCacheScope(store.state);
    let projectEvents = snapshot(store.state.project?.project?.events);
    let globalEvents = snapshot(store.state.event?.globalEvents);
    const writes = new WeakMap();
    const invalidate = (events) => store.commit(INVALIDATE_EVENT_USER_DAILY_DIRECTIONS, events);

    store.subscribe((mutation, state) => {
        const nextScope = dailyDirectionCacheScope(state);
        if (scope !== nextScope || mutation.type === "RESET" ||
            (mutation.type === "auth/SET_AUTHENTICATED" && !mutation.payload)) {
            scope = nextScope;
            cache.clear();
            projectEvents = snapshot(state.project?.project?.events);
            globalEvents = snapshot(state.event?.globalEvents);
            invalidate([]);
            return;
        }

        if (affectsRoute(mutation)) {
            const event = mutation.payload;
            const id = String(event?.id ?? event);
            invalidate([projectEvents.get(id), globalEvents.get(id), event]);
            if ([REMOVE_EVENT, REMOVE_PROSPECT_EVENT].includes(mutation.type)) {
                projectEvents.delete(id);
                globalEvents.delete(id);
            } else if (event?.id != null) {
                const current = state.project?.project?.events?.find((item) => item?.id == event.id);
                const next = snapshot([{ ...projectEvents.get(id), ...current, ...event }]).get(id);
                projectEvents.set(id, next);
            }
        } else if ([SET_EVENTS, SET_GLOBAL_EVENTS].includes(mutation.type)) {
            const previous = mutation.type === SET_EVENTS ? projectEvents : globalEvents;
            const next = snapshot(mutation.payload);
            const changed = [];
            // A missing item may just be outside the new filters or a view reset.
            // Retain snapshots across those resets; actual DELETE mutations invalidate.
            for (const id of next.keys()) {
                if (dailyDirectionEventsSignature([previous.get(id)]) !== dailyDirectionEventsSignature([next.get(id)])) {
                    changed.push(previous.get(id), next.get(id));
                }
                previous.set(id, next.get(id));
            }
            if (changed.length) invalidate(changed);
        }
    });

    function finish(action) {
        const write = writes.get(action);
        if (!write) return;
        writes.delete(action);
        if (write.scope === scope) {
            cache.deferCaching([...write.events, action.payload]);
            invalidate([action.payload]);
        }
        write.release();
    }

    store.subscribeAction({
        before(action) {
            if (affectsRoute(action)) {
                const id = String(action.payload?.id ?? action.payload);
                const events = [projectEvents.get(id), globalEvents.get(id), action.payload];
                cache.deferCaching(events);
                writes.set(action, { scope, events, release: cache.beginWrite() });
                invalidate([action.payload]);
            }
        },
        after: finish,
        error: finish,
    });
}
