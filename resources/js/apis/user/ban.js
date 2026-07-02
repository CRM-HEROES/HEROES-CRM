import ApiService from "@/apis/api.service";

export default {
    ban(user) {
        return ApiService.put(`user/${user}/ban`);
    },
    cancelBan(user) {
        return ApiService.delete(`user/${user}/ban`);
    },
};
