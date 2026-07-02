import ApiService from "@/apis/api.service";

export default {
    get(params) {
        return ApiService.get(`event`, params);
    },
};
