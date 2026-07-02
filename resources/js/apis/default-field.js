import ApiService from "@/apis/api.service";

export default {
    get(params) {
        return ApiService.get("default-field", params);
    },
};
