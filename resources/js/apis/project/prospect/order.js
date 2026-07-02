import ApiService from "@/apis/api.service";

export default {
    get(project, prospect, params) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/order`,
            params
        );
    },
    create(project, prospect, params) {
        return ApiService.post(
            `project/${project}/prospect/${prospect}/order`,
            params
        );
    },
    show(project, prospect, order) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/order/${order}`
        );
    },
    update(project, prospect, order, params) {
        return ApiService.put(
            `project/${project}/prospect/${prospect}/order/${order}`,
            params
        );
    },
    destroy(project, prospect, order) {
        return ApiService.delete(
            `project/${project}/prospect/${prospect}/order/${order}`
        );
    },
};
