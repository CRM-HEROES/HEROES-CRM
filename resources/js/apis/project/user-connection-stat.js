import ApiService from "@/apis/api.service";

export default {
    get(project) {
        return ApiService.get(`project/${project}/user/connection-stat`);
    },
    show(project, date) {
        return ApiService.get(
            `project/${project}/user/connection-stat/${date}`
        );
    },
};
