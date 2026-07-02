import ApiService from "@/apis/api.service";

export default {
    get() {
        return ApiService.get("google/calendar");
    },
    destroy(slug) {
        return ApiService.delete(`google/calendar/${slug}`);
    },
};
