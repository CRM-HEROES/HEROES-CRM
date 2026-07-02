import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/role`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/role`, params);
    },
    show(project, role) {
        return ApiService.get(`project/${project}/role/${role}`);
    },
    update(project, role, params) {
        return ApiService.put(`project/${project}/role/${role}`, params);
    },
    destroy(project, role) {
        return ApiService.delete(`project/${project}/role/${role}`);
    },
};
