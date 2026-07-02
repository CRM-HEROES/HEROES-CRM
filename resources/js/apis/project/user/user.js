import ApiService from "@/apis/api.service";

export default {
    get(project, user, params) {
        return ApiService.get(
            `project/${project}/user/${user}/assignable-user`,
            params
        );
    },
    update(project, user, assignableUser) {
        return ApiService.put(
            `project/${project}/user/${user}/assignable-user/${assignableUser}`
        );
    },
    destroy(project, user, assignableUser) {
        return ApiService.delete(
            `project/${project}/user/${user}/assignable-user/${assignableUser}`
        );
    },
    bulkUpdate(project, users, assignableUsers) {
        return ApiService.put(`project/${project}/user/assignable-user/bulk`, {
            action: "attach",
            users: users,
            assignableUsers: assignableUsers,
        });
    },
    bulkDestroy(project, users, assignableUsers) {
        return ApiService.put(`project/${project}/user/assignable-user/bulk`, {
            action: "detach",
            users: users,
            assignableUsers: assignableUsers,
        });
    },
};
