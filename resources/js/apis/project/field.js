import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/field`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/field`, params);
    },
    show(project, field) {
        return ApiService.get(`project/${project}/field/${field}`);
    },
    update(project, field, params) {
        return ApiService.put(`project/${project}/field/${field}`, params);
    },
    destroy(project, field) {
        return ApiService.delete(`project/${project}/field/${field}`);
    },
    category(project, field) {
        return ApiService.put(`project/${project}/field/${field}/category`);
    },
    orders(project, params) {
        return ApiService.put(`project/${project}/field/orders`, params);
    },
};
