import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/folder`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/folder`, params);
    },
    show(project, folder) {
        return ApiService.get(`project/${project}/folder/${folder}`);
    },
    update(project, folder, params) {
        return ApiService.put(`project/${project}/folder/${folder}`, params);
    },
    destroy(project, folder) {
        return ApiService.delete(`project/${project}/folder/${folder}`);
    },
};
