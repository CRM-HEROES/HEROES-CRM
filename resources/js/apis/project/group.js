import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/group`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/group`, params);
    },
    show(project, group) {
        return ApiService.get(`project/${project}/group/${group}`);
    },
    update(project, group, params) {
        return ApiService.put(`project/${project}/group/${group}`, params);
    },
    destroy(project, group) {
        return ApiService.delete(`project/${project}/group/${group}`);
    },
};
