import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/metric`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/metric`, params);
    },
    show(project, metric) {
        return ApiService.get(`project/${project}/metric/${metric}`);
    },
    update(project, metric, params) {
        return ApiService.put(`project/${project}/metric/${metric}`, params);
    },
    destroy(project, metric) {
        return ApiService.delete(`project/${project}/metric/${metric}`);
    },
};
