import ApiService from "@/apis/api.service";

export default {
    show(user, setting) {
        return ApiService.get(`user/${user}/setting/${setting}`);
    },
    update(user, setting, params) {
        return ApiService.put(`user/${user}/setting/${setting}`, {
            value: params,
        });
    },
    destroy(user, setting) {
        return ApiService.delete(`user/${user}/setting/${setting}`);
    },
};
