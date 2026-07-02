import ApiService from "@/apis/api.service";

export default {
    get(project, user, params) {
        return ApiService.get(`project/${project}/user/${user}/label`, params);
    },
    users(project, label) {
        return ApiService.get(`project/${project}/label/${label}/user`);
    },
    update(project, user, label) {
        return ApiService.put(`project/${project}/user/${user}/label/${label}`);
    },
    destroy(project, user, label) {
        return ApiService.delete(
            `project/${project}/user/${user}/label/${label}`
        );
    },
    bulkUpdate(project, users, labels) {
        return ApiService.put(`project/${project}/user/label/bulk`, {
            action: "attach",
            users: users,
            labels: labels,
        });
    },
    bulkDestroy(project, users, labels) {
        return ApiService.put(`project/${project}/user/label/bulk`, {
            action: "detach",
            users: users,
            labels: labels,
        });
    },
};
