import ApiService from "@/apis/api.service";

export default {
    show() {
        return ApiService.get("settings/claude");
    },
    update(apiKey) {
        return ApiService.put("settings/claude", { api_key: apiKey });
    },
};
