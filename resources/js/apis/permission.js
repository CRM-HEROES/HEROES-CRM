import ApiService from "@/apis/api.service";

export default {
    get(project) {
        if (project) {
            return ApiService.get(`project/${project}/permission`);
        } else {
            return ApiService.get("permission");
        }
    },
};
