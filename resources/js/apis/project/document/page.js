import ApiService from "@/apis/api.service";

export default {
    get(project, document, params) {
        return ApiService.get(
            `project/${project}/document/${document}/page`,
            params
        );
    },
    create(project, document, params) {
        return ApiService.post(
            `project/${project}/document/${document}/page`,
            params
        );
    },
    show(project, document, page) {
        return ApiService.get(
            `project/${project}/document/${document}/page/${page}`
        );
    },
    update(project, document, page, params) {
        return ApiService.put(
            `project/${project}/document/${document}/page/${page}`,
            params
        );
    },
    destroy(project, document, page) {
        return ApiService.delete(
            `project/${project}/document/${document}/page/${page}`
        );
    },
};
