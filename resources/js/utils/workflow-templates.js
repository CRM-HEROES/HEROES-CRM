/**
 * Ready-made workflows: a starting point to adapt, not a finished product.
 * Operators and texts are left empty on purpose (they belong to the project);
 * the builder says what is still to fill in before turning it on.
 *
 * Names and descriptions: workflow.template.<id>.name / .description
 */
import { layout } from "./workflow-graph";

const step = (id, type, config = {}) => ({ id, type, name: null, config, position: { x: 0, y: 0 } });

const WORK_HOURS = { check: "work_hours", days: [1, 2, 3, 4, 5], from: "08:00", to: "17:00" };

const TEMPLATES = {
    welcome_email: () => ({
        nodes: [step("trigger", "trigger", { trigger: "prospect.created" }), step("action1", "action", { action: "send_email" })],
        edges: [{ from: "trigger", to: "action1" }],
    }),

    sms_follow_up: () => ({
        nodes: [
            step("trigger", "trigger", { trigger: "prospect.created" }),
            step("action1", "action", { action: "send_sms" }),
            step("event1", "wait_event", { event: "sms_received", days: 2 }),
            step("end1", "end"),
            step("action2", "action", { action: "send_sms" }),
            step("end2", "end"),
        ],
        edges: [
            { from: "trigger", to: "action1" },
            { from: "action1", to: "event1" },
            { from: "event1", to: "end1", branch: "yes" },
            { from: "event1", to: "action2", branch: "no" },
            { from: "action2", to: "end2" },
        ],
    }),

    appointment_reminder: () => ({
        nodes: [step("trigger", "trigger", { trigger: "event.starting_soon", minutes_before: 1440 }), step("action1", "action", { action: "send_sms" })],
        edges: [{ from: "trigger", to: "action1" }],
    }),

    work_hours_contact: () => ({
        nodes: [
            step("trigger", "trigger", { trigger: "prospect.created" }),
            step("condition1", "condition", { op: "and", items: [{ ...WORK_HOURS }] }),
            step("action1", "action", { action: "send_sms" }),
            step("wait1", "wait", { until_time: "08:00" }),
            step("action2", "action", { action: "send_sms" }),
        ],
        edges: [
            { from: "trigger", to: "condition1" },
            { from: "condition1", to: "action1", branch: "yes" },
            { from: "condition1", to: "wait1", branch: "no" },
            { from: "wait1", to: "action2" },
        ],
    }),
};

export const TEMPLATE_IDS = Object.keys(TEMPLATES);

/**
 * A new graph of the template, arranged; null for an unknown template
 */
export function templateGraph(id) {
    return TEMPLATES[id] ? layout(TEMPLATES[id]()) : null;
}
