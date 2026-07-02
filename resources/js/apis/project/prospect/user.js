import ApiService from "@/apis/api.service";

export default {
    get(project, prospect, params) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/user`,
            params
        );
    },
    update(project, prospect, user) {
        return ApiService.put(
            `project/${project}/prospect/${prospect}/user/${user}`
        );
    },
    destroy(project, prospect, user) {
        return ApiService.delete(
            `project/${project}/prospect/${prospect}/user/${user}`
        );
    },
    bulkUpdate(project, filters, users) {
        return ApiService.put(`project/${project}/prospect/user/bulk`, {
            action: "attach",
            filters,
            users,
        });
    },
    bulkDestroy(project, filters, users) {
        return ApiService.put(`project/${project}/prospect/user/bulk`, {
            action: "detach",
            filters,
            users,
        });
    },
};
