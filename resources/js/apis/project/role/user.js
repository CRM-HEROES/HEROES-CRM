import ApiService from "@/apis/api.service";

export default {
    get(project, role, params) {
        return ApiService.get(
            `project/${project}/role/${role}/assignable-user`,
            params
        );
    },
    update(project, role, assignableRole) {
        return ApiService.put(
            `project/${project}/role/${role}/assignable-user/${assignableRole}`
        );
    },
    destroy(project, role, assignableRole) {
        return ApiService.delete(
            `project/${project}/role/${role}/assignable-user/${assignableRole}`
        );
    },
    bulkUpdate(project, roles, assignableRoles) {
        return ApiService.put(`project/${project}/role/assignable-user/bulk`, {
            action: "attach",
            roles: roles,
            assignableRoles: assignableRoles,
        });
    },
    bulkDestroy(project, roles, assignableRoles) {
        return ApiService.put(`project/${project}/role/assignable-user/bulk`, {
            action: "detach",
            roles: roles,
            assignableRoles: assignableRoles,
        });
    },
};
