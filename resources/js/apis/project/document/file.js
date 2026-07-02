import ApiService from "@/apis/api.service";

export default {
    get(project, document, params) {
        return ApiService.get(
            `project/${project}/document/${document}/file`,
            params
        );
    },
    create(project, document, params) {
        return ApiService.post(
            `project/${project}/document/${document}/file`,
            params,
            {
                headers: {
                    "content-type": "multipart/form-data",
                },
            }
        );
    },
    show(project, document, file) {
        return ApiService.get(
            `project/${project}/document/${document}/file/${file}`
        );
    },
    update(project, document, file, params) {
        return ApiService.post(
            `project/${project}/document/${document}/file/${file}`,
            params,
            {
                headers: {
                    "content-type": "multipart/form-data",
                },
            }
        );
    },
    destroy(project, document, file) {
        return ApiService.delete(
            `project/${project}/document/${document}/file/${file}`
        );
    },
};
