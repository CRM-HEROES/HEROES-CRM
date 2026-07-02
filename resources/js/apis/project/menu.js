import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/menu`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/menu`, params);
    },
    show(project, menu) {
        return ApiService.get(`project/${project}/menu/${menu}`);
    },
    update(project, menu, params) {
        return ApiService.put(`project/${project}/menu/${menu}`, params);
    },
    destroy(project, menu) {
        return ApiService.delete(`project/${project}/menu/${menu}`);
    },
};
