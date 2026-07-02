import ApiService from "@/apis/api.service";

export default {
    get(user) {
        return ApiService.get(`user/${user}/session`);
    },
    geoips(user) {
        return ApiService.get(`user/${user}/session/geoip`);
    },
    ban(user, session) {
        return ApiService.post(`user/${user}/session/${session}/ban`);
    },
    confirmBan(user, session, params) {
        return ApiService.put(`user/${user}/session/${session}/ban`, params);
    },
    cancelBan(user, session) {
        return ApiService.delete(`user/${user}/session/${session}/ban`);
    },
};
