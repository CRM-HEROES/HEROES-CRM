import ApiService from "@/apis/api.service";

export default {
    get(project, role, params) {
        return ApiService.get(
            `project/${project}/role/${role}/order-action`,
            params
        );
    },
    update(project, role, orderAction) {
        return ApiService.put(
            `project/${project}/role/${role}/order-action/${orderAction}`
        );
    },
    destroy(project, role, orderAction) {
        return ApiService.delete(
            `project/${project}/role/${role}/order-action/${orderAction}`
        );
    },
    bulkUpdate(project, roles, orderActions) {
        return ApiService.put(`project/${project}/role/order-action/bulk`, {
            action: "attach",
            roles: roles,
            orderActions: orderActions,
        });
    },
    bulkDestroy(project, roles, orderActions) {
        return ApiService.put(`project/${project}/role/order-action/bulk`, {
            action: "detach",
            roles: roles,
            orderActions: orderActions,
        });
    },
};
