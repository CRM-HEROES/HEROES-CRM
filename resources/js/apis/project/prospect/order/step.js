import ApiService from "@/apis/api.service";

export default {
    get(project, prospect, order, params) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/order/${order}/step`,
            params
        );
    },
    update(project, prospect, order, step) {
        return ApiService.put(
            `project/${project}/prospect/${prospect}/order/${order}/step/${step}`
        );
    },
    destroy(project, prospect, order, step) {
        return ApiService.delete(
            `project/${project}/prospect/${prospect}/order/${order}/step/${step}`
        );
    },
};
