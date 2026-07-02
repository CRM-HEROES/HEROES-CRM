import ApiService from "@/apis/api.service";

export default {
    get(project, user, params) {
        return ApiService.get(
            `project/${project}/user/${user}/order-step`,
            params
        );
    },
    users(project, orderStep) {
        return ApiService.get(
            `project/${project}/order-step/${orderStep}/user`
        );
    },
    update(project, user, orderStep) {
        return ApiService.put(
            `project/${project}/user/${user}/order-step/${orderStep}`
        );
    },
    destroy(project, user, orderStep) {
        return ApiService.delete(
            `project/${project}/user/${user}/order-step/${orderStep}`
        );
    },
    bulkUpdate(project, users, orderSteps) {
        return ApiService.put(`project/${project}/user/order-step/bulk`, {
            action: "attach",
            users: users,
            orderSteps: orderSteps,
        });
    },
    bulkDestroy(project, users, orderSteps) {
        return ApiService.put(`project/${project}/user/order-step/bulk`, {
            action: "detach",
            users: users,
            orderSteps: orderSteps,
        });
    },
};
