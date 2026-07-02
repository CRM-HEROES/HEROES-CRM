import ApiService from "@/apis/api.service";

export default {
    get(project, user, params) {
        return ApiService.get(
            (project ? `project/${project}/` : "") +
                `user/${user}/daily-direction`,
            params
        );
    },
};
