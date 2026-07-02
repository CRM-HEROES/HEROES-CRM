import ApiService from "@/apis/api.service";

export default {
    get(project, user, params) {
        return ApiService.get(`project/${project}/user/${user}/action`, params);
    },
};
