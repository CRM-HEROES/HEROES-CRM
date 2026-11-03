import ApiService from "@/apis/api.service";

/**
 * AI agents: the agents of the user, the conversations and the actions
 * waiting for a human decision (see routes/api.php, prefix "agent").
 */
export default {
    catalog() {
        return ApiService.get("agent/catalog");
    },
    list() {
        return ApiService.get("agent");
    },
    create(agent) {
        return ApiService.post("agent", agent);
    },
    update(id, agent) {
        return ApiService.put(`agent/${id}`, agent);
    },
    remove(id) {
        return ApiService.delete(`agent/${id}`);
    },
    ask(id, project, message, conversationId) {
        return ApiService.post(`agent/${id}/ask`, {
            project,
            message,
            conversation_id: conversationId || null,
        });
    },
    conversation(id) {
        return ApiService.get(`agent/conversation/${id}`);
    },
    run(id) {
        return ApiService.get(`agent/run/${id}`);
    },
    actions(status = "pending") {
        return ApiService.get("agent/action", { params: { status } });
    },
    approve(id) {
        return ApiService.post(`agent/action/${id}/approve`);
    },
    reject(id) {
        return ApiService.post(`agent/action/${id}/reject`);
    },
};
