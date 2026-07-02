import ApiService from "@/apis/api.service";

export default {
    get(project, role, params) {
        return ApiService.get(`project/${project}/role/${role}/label`, params);
    },
    update(project, role, label) {
        return ApiService.put(`project/${project}/role/${role}/label/${label}`);
    },
    destroy(project, role, label) {
        return ApiService.delete(
            `project/${project}/role/${role}/label/${label}`
        );
    },
    bulkUpdate(project, roles, labels) {
        return ApiService.put(`project/${project}/role/label/bulk`, {
            action: "attach",
            roles: roles,
            labels: labels,
        });
    },
    bulkDestroy(project, roles, labels) {
        return ApiService.put(`project/${project}/role/label/bulk`, {
            action: "detach",
            roles: roles,
            labels: labels,
        });
    },
};
