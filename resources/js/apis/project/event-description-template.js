import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(
            `project/${project}/event-description-template`,
            params
        );
    },
    create(project, params) {
        return ApiService.post(
            `project/${project}/event-description-template`,
            params
        );
    },
    show(project, eventDescriptionTemplate) {
        return ApiService.get(
            `project/${project}/event-description-template/${eventDescriptionTemplate}`
        );
    },
    update(project, eventDescriptionTemplate, params) {
        return ApiService.put(
            `project/${project}/event-description-template/${eventDescriptionTemplate}`,
            params
        );
    },
    destroy(project, eventDescriptionTemplate) {
        return ApiService.delete(
            `project/${project}/event-description-template/${eventDescriptionTemplate}`
        );
    },
};
