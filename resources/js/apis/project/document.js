import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/document`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/document`, params);
    },
    show(project, document) {
        return ApiService.get(`project/${project}/document/${document}`);
    },
    update(project, document, params) {
        return ApiService.put(
            `project/${project}/document/${document}`,
            params
        );
    },
    destroy(project, document) {
        return ApiService.delete(`project/${project}/document/${document}`);
    },
};
