import ApiService from "@/apis/api.service";

export default {
    get(project, prospect, params) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/link`,
            params
        );
    },
    create(project, prospect, params) {
        return ApiService.post(
            `project/${project}/prospect/${prospect}/link`,
            params
        );
    },
    show(project, prospect, link) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/link/${link}`
        );
    },
    update(project, prospect, link, params) {
        return ApiService.put(
            `project/${project}/prospect/${prospect}/link/${link}`,
            params
        );
    },
    destroy(project, prospect, link) {
        return ApiService.delete(
            `project/${project}/prospect/${prospect}/link/${link}`
        );
    },
};
