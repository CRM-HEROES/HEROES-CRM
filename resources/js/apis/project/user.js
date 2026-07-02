import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/user`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/user`, params);
    },
    show(project, user) {
        return ApiService.get(`project/${project}/user/${user}`);
    },
    update(project, user, params) {
        return ApiService.put(`project/${project}/user/${user}`, params);
    },
    destroy(project, user) {
        return ApiService.delete(`project/${project}/user/${user}`);
    },
    bulkDestroy(project, users) {
        return ApiService.put(`project/${project}/user/bulk`, {
            action: "delete",
            users: users,
        });
    },
};
