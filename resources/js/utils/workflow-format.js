/**
 * Display helpers of the workflow follow-up screens (no Vue, no store).
 */

/**
 * 90 => "1 min 30 s", 7200 => "2 h", null => "-"
 */
export function duration(seconds) {
    if (seconds === null || seconds === undefined) {
        return "-";
    }

    const total = Math.max(0, Math.round(seconds));
    const hours = Math.floor(total / 3600);
    const minutes = Math.floor((total % 3600) / 60);
    const rest = total % 60;

    if (hours) {
        return minutes ? `${hours} h ${minutes} min` : `${hours} h`;
    }

    if (minutes) {
        return rest ? `${minutes} min ${rest} s` : `${minutes} min`;
    }

    return `${rest} s`;
}

/**
 * A date from the API in the user's locale, "-" when there is none
 */
export function dateTime(value) {
    if (!value) {
        return "-";
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return "-";
    }

    return date.toLocaleString(undefined, {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

/**
 * Sign shown in front of a step, by its outcome
 */
export function stepIcon(status) {
    return { completed: "✓", failed: "✗", skipped: "–", running: "…", waiting: "⏳" }[status] || "·";
}

/**
 * Executions that can still be cancelled (a running one is in a worker's hands)
 */
export function cancellable(execution) {
    return execution.status === "pending" || execution.status === "waiting";
}

/**
 * What an execution waits for, as a translation key suffix
 * (workflow.monitor.<key>), null when it does not wait
 */
export function waitingKind(execution) {
    if (execution.status !== "waiting") {
        return null;
    }

    return execution.waiting_event ? "waiting_event" : "waiting_until";
}
