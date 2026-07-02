import ApiService from "@/apis/api.service";

export default {
    get(project, user, params) {
        return ApiService.get(`project/${project}/user/${user}/group`, params);
    },
    users(project, group) {
        return ApiService.get(`project/${project}/group/${group}/user`);
    },
    update(project, user, group) {
        return ApiService.put(`project/${project}/user/${user}/group/${group}`);
    },
    destroy(project, user, group) {
        return ApiService.delete(
            `project/${project}/user/${user}/group/${group}`
        );
    },
    bulkUpdate(project, users, groups) {
        return ApiService.put(`project/${project}/user/group/bulk`, {
            action: "attach",
            users: users,
            groups: groups,
        });
    },
    bulkDestroy(project, users, groups) {
        return ApiService.put(`project/${project}/user/group/bulk`, {
            action: "detach",
            users: users,
            groups: groups,
        });
    },
};
