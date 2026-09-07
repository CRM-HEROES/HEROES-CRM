import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/line`, params);
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
    destroy(project, line) {
        return ApiService.delete(`project/${project}/line/${line}`);
    },
};
