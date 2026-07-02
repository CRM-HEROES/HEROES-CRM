import ApiService from "@/apis/api.service";

export default {
    get(project, user, params) {
        return ApiService.get(`project/${project}/user/${user}/role`, params);
    },
    users(project, role) {
        return ApiService.get(`project/${project}/role/${role}/user`);
    },
    update(project, user, role) {
        return ApiService.put(`project/${project}/user/${user}/role/${role}`);
    },
    destroy(project, user, role) {
        return ApiService.delete(
            `project/${project}/user/${user}/role/${role}`
        );
    },
    bulkUpdate(project, users, roles) {
        return ApiService.put(`project/${project}/user/role/bulk`, {
            action: "attach",
            users: users,
            roles: roles,
        });
    },
    bulkDestroy(project, users, roles) {
        return ApiService.put(`project/${project}/user/role/bulk`, {
            action: "detach",
            users: users,
            roles: roles,
        });
    },
};
