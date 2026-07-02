import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/thread`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/thread`, params);
    },
    show(project, thread) {
        return ApiService.get(`project/${project}/thread/${thread}`);
    },
    update(project, thread, params) {
        return ApiService.put(`project/${project}/thread/${thread}`, params);
    },
    destroy(project, thread) {
        return ApiService.delete(`project/${project}/thread/${thread}`);
    },
};
