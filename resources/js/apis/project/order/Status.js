import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/order-status`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/order-status`, params);
    },
    show(project, status) {
        return ApiService.get(`project/${project}/order-status/${status}`);
    },
    update(project, status, params) {
        return ApiService.put(
            `project/${project}/order-status/${status}`,
            params
        );
    },
    destroy(project, status) {
        return ApiService.delete(`project/${project}/order-status/${status}`);
    },
};
