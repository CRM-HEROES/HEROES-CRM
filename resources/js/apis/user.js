import ApiService from "@/apis/api.service";

export default {
    get(params) {
        return ApiService.get(`user`, params);
    },
    email(email) {
        return ApiService.get(`user/email/${email}`);
    },
    create(params) {
        return ApiService.post(`user`, params);
    },
    show(user) {
        return ApiService.get(`user/${user}`);
    },
    update(user, params) {
        return ApiService.put(`user/${user}`, params);
    },
};
