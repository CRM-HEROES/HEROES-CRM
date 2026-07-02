import ApiService from "@/apis/api.service";

export default {
    get(project, user, params) {
        return ApiService.get(`project/${project}/user/${user}/folder`, params);
    },
    users(project, folder) {
        return ApiService.get(`project/${project}/folder/${folder}/user`);
    },
    update(project, user, folder) {
        return ApiService.put(
            `project/${project}/user/${user}/folder/${folder}`
        );
    },
    destroy(project, user, folder) {
        return ApiService.delete(
            `project/${project}/user/${user}/folder/${folder}`
        );
    },
    bulkUpdate(project, users, folders) {
        return ApiService.put(`project/${project}/user/folder/bulk`, {
            action: "attach",
            users: users,
            folders: folders,
        });
    },
    bulkDestroy(project, users, folders) {
        return ApiService.put(`project/${project}/user/folder/bulk`, {
            action: "detach",
            users: users,
            folders: folders,
        });
    },
};
