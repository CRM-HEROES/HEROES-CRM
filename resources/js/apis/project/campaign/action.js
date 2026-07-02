import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/campaign-action`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/campaign-action`, params);
    },
    show(project, action) {
        return ApiService.get(`project/${project}/campaign-action/${action}`);
    },
    update(project, action, params) {
        return ApiService.put(
            `project/${project}/campaign-action/${action}`,
            params
        );
    },
    destroy(project, action) {
        return ApiService.delete(
            `project/${project}/campaign-action/${action}`
        );
    },
};
