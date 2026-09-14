import ApiService from "@/apis/api.service";

export default {
    index(project) {
        return ApiService.get(`project/${project}/ai-agent`);
    },
    show(project, agent) {
        return ApiService.get(`project/${project}/ai-agent/${agent}`);
    },
    create(project, payload) {
        return ApiService.post(`project/${project}/ai-agent`, payload);
    },
    update(project, agent, payload) {
        return ApiService.put(`project/${project}/ai-agent/${agent}`, payload);
    },
    destroy(project, agent) {
        return ApiService.delete(`project/${project}/ai-agent/${agent}`);
    },
};
