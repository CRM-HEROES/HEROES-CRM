import ApiService from "@/apis/api.service";

export default {
    check() {
        return ApiService.get("google/authenticator/check");
    },
    deactivate(slug) {
        return ApiService.post(`google/authenticator/deactivate`);
    },
};
