import ApiService from "@/apis/api.service";

export default {
    lock(user) {
        return ApiService.put(`user/${user}/lock`);
    },
    unlock(user) {
        return ApiService.delete(`user/${user}/lock`);
    },
};
