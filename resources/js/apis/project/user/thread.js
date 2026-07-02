import ApiService from "@/apis/api.service";

export default {
    get(project, user, params) {
        return ApiService.get(`project/${project}/user/${user}/thread`, params);
    },
    users(project, thread) {
        return ApiService.get(`project/${project}/thread/${thread}/user`);
    },
    update(project, user, thread) {
        return ApiService.put(
            `project/${project}/user/${user}/thread/${thread}`
        );
    },
    destroy(project, user, thread) {
        return ApiService.delete(
            `project/${project}/user/${user}/thread/${thread}`
        );
    },
    bulkUpdate(project, users, threads) {
        return ApiService.put(`project/${project}/user/thread/bulk`, {
            action: "attach",
            users: users,
            threads: threads,
        });
    },
    bulkDestroy(project, users, threads) {
        return ApiService.put(`project/${project}/user/thread/bulk`, {
            action: "detach",
            users: users,
            threads: threads,
        });
    },
};
