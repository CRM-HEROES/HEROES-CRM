import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/order-action`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/order-action`, params);
    },
    show(project, action) {
        return ApiService.get(`project/${project}/order-action/${action}`);
    },
    update(project, action, params) {
        return ApiService.put(
            `project/${project}/order-action/${action}`,
            params
        );
    },
    destroy(project, action) {
        return ApiService.delete(`project/${project}/order-action/${action}`);
    },
};
