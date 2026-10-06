import ApiService from "@/apis/api.service";

export default {
    overview() {
        return ApiService.get("monitoring/overview");
    },
    history(period) {
        return ApiService.get("monitoring/history", { params: { period } });
    },
};
