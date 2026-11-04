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
    get(id) {
        return ApiService.get(`agent/${id}`);
    },
    create(agent) {
        return ApiService.post("agent", agent);
    },
    update(id, agent) {
        return ApiService.put(`agent/${id}`, agent);
    },
    // The picture of an agent (multipart: the file is re-encoded by the server)
    uploadAvatar(id, file) {
        const body = new FormData();
        body.append("photo", file);

        return ApiService.post(`agent/${id}/avatar`, body);
    },
    removeAvatar(id) {
        return ApiService.delete(`agent/${id}/avatar`);
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
    // The history: every conversation of the user in a project
    conversations(project) {
        return ApiService.get("agent/conversation", { params: { project } });
    },
    conversation(id) {
        return ApiService.get(`agent/conversation/${id}`);
    },
    archiveConversation(id) {
        return ApiService.delete(`agent/conversation/${id}`);
    },
    run(id) {
        return ApiService.get(`agent/run/${id}`);
    },
    // A document an agent produced
    report(id) {
        return ApiService.get(`agent/report/${id}`);
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
