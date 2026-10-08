import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/line`, params);
    },
    availableUsers(project, params) {
        return ApiService.get(`project/${project}/line/available-users`, {
            params,
        });
    },
    create(project, params) {
        return ApiService.post(`project/${project}/line`, params);
    },
    show(project, line) {
        return ApiService.get(`project/${project}/line/${line}`);
    },
    update(project, line, params) {
        return ApiService.put(`project/${project}/line/${line}`, params);
    },
    makeCloudtalkCall(project, params) {
        return ApiService.post(`project/${project}/line/cloudtalk/call`, params);
    },
    lookupCloudtalkCall(project, params) {
        return ApiService.post(
            `project/${project}/line/cloudtalk/lookup`,
            params
        );
    },
    fetchCloudtalkCallHistory(project, params) {
        return ApiService.post(
            `project/${project}/line/cloudtalk/history`,
            params
        );
    },
    verifyCloudtalk(project, params) {
        return ApiService.post(`project/${project}/line/cloudtalk/verify`, params);
    },
    getCloudtalkAgents(project, params) {
        return ApiService.post(`project/${project}/line/cloudtalk/agents`, params);
    },
    destroy(project, line) {
        return ApiService.delete(`project/${project}/line/${line}`);
    },
};
