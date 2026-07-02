import ApiService from "@/apis/api.service";

export default {
    duplicate(project, user, params) {
        return ApiService.put(
            `project/${project}/user/${user}/duplicate`,
            params
        );
    },
};
