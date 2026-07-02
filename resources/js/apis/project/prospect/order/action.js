import ApiService from "@/apis/api.service";

export default {
    get(project, prospect, order, params) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/order/${order}/action`,
            params
        );
    },
    update(project, prospect, order, action, params) {
        return ApiService.put(
            `project/${project}/prospect/${prospect}/order/${order}/action/${action}`,
            params
        );
    },
    destroy(project, prospect, order, action) {
        return ApiService.delete(
            `project/${project}/prospect/${prospect}/order/${order}/action/${action}`
        );
    },
};
