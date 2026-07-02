import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/message-template`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/message-template`, params);
    },
    show(project, messageTemplate) {
        return ApiService.get(
            `project/${project}/message-template/${messageTemplate}`
        );
    },
    update(project, messageTemplate, params) {
        return ApiService.put(
            `project/${project}/message-template/${messageTemplate}`,
            params
        );
    },
    destroy(project, messageTemplate) {
        return ApiService.delete(
            `project/${project}/message-template/${messageTemplate}`
        );
    },
};
