import ApiService from "@/apis/api.service";

export default {
    get(project, user, params) {
        return ApiService.get(
            `project/${project}/user/${user}/order-action`,
            params
        );
    },
    update(project, user, orderAction) {
        return ApiService.put(
            `project/${project}/user/${user}/order-action/${orderAction}`
        );
    },
    destroy(project, user, orderAction) {
        return ApiService.delete(
            `project/${project}/user/${user}/order-action/${orderAction}`
        );
    },
    bulkUpdate(project, users, orderActions) {
        return ApiService.put(`project/${project}/user/order-action/bulk`, {
            action: "attach",
            users: users,
            orderActions: orderActions,
        });
    },
    bulkDestroy(project, users, orderActions) {
        return ApiService.put(`project/${project}/user/order-action/bulk`, {
            action: "detach",
            users: users,
            orderActions: orderActions,
        });
    },
};
