import ApiService from "@/apis/api.service";

export default {
    get(project, role, params) {
        return ApiService.get(
            `project/${project}/role/${role}/order-step`,
            params
        );
    },
    update(project, role, orderStep) {
        return ApiService.put(
            `project/${project}/role/${role}/order-step/${orderStep}`
        );
    },
    destroy(project, role, orderStep) {
        return ApiService.delete(
            `project/${project}/role/${role}/order-step/${orderStep}`
        );
    },
    bulkUpdate(project, roles, orderSteps) {
        return ApiService.put(`project/${project}/role/order-step/bulk`, {
            action: "attach",
            roles: roles,
            orderSteps: orderSteps,
        });
    },
    bulkDestroy(project, roles, orderSteps) {
        return ApiService.put(`project/${project}/role/order-step/bulk`, {
            action: "detach",
            roles: roles,
            orderSteps: orderSteps,
        });
    },
};
