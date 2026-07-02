import ApiService from "@/apis/api.service";

export default {
    get(user, params) {
        return ApiService.get(`user/${user}/project`, params);
    },
    users(project) {
        return ApiService.get(`project/${project}/user`);
    },
    update(email, project) {
        return ApiService.post(`project/${project}/user`, {
            email,
        });
    },
    destroy(user, project) {
        return ApiService.delete(`project/${project}/user/${user}`);
    },
};
