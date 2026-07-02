import ApiService from "@/apis/api.service";

export default {
    get() {
        return ApiService.get("google/contact");
    },
    destroy(slug) {
        return ApiService.delete(`google/contact/${slug}`);
    },
};
