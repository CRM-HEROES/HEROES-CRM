import ApiService from "@/apis/api.service";

export default {
    get(project, document, params) {
        return ApiService.get(
            `project/${project}/document/${document}/font`,
            params
        );
    },
    create(project, document, params) {
        return ApiService.post(
            `project/${project}/document/${document}/font`,
            params
        );
    },
    show(project, document, font) {
        return ApiService.get(
            `project/${project}/document/${document}/font/${font}`
        );
    },
    update(project, document, font, params) {
        return ApiService.put(
            `project/${project}/document/${document}/font/${font}`,
            params
        );
    },
    destroy(project, document, font) {
        return ApiService.delete(
            `project/${project}/document/${document}/font/${font}`
        );
    },
};
