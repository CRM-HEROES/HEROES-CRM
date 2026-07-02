import ApiService from "@/apis/api.service";

export default {
    get(project, prospect, params) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/event`,
            params
        );
    },
    create(project, prospect, params) {
        return ApiService.post(
            `project/${project}/prospect/${prospect}/event`,
            params
        );
    },
    show(project, prospect, event) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/event/${event}`
        );
    },
    update(project, prospect, event, params) {
        return ApiService.put(
            `project/${project}/prospect/${prospect}/event/${event}`,
            params
        );
    },
    destroy(project, prospect, event) {
        return ApiService.delete(
            `project/${project}/prospect/${prospect}/event/${event}`
        );
    },
};
