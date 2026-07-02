import ApiService from "@/apis/api.service";

export default {
    get(project, document, params) {
        return ApiService.get(
            `project/${project}/document/${document}/field`,
            params
        );
    },
    create(project, document, params) {
        return ApiService.post(
            `project/${project}/document/${document}/field`,
            params
        );
    },
    show(project, document, field) {
        return ApiService.get(
            `project/${project}/document/${document}/field/${field}`
        );
    },
    update(project, document, field, params) {
        return ApiService.put(
            `project/${project}/document/${document}/field/${field}`,
            params
        );
    },
    destroy(project, document, field) {
        return ApiService.delete(
            `project/${project}/document/${document}/field/${field}`
        );
    },
};
