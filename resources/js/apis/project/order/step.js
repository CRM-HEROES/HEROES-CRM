import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/order-step`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/order-step`, params);
    },
    show(project, step) {
        return ApiService.get(`project/${project}/order-step/${step}`);
    },
    update(project, step, params) {
        return ApiService.put(`project/${project}/order-step/${step}`, params);
    },
    destroy(project, step) {
        return ApiService.delete(`project/${project}/order-step/${step}`);
    },
};
