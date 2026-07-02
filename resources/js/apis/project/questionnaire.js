import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/questionnaire`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/questionnaire`, params);
    },
    show(project, questionnaire) {
        return ApiService.get(
            `project/${project}/questionnaire/${questionnaire}`
        );
    },
    update(project, questionnaire, params) {
        return ApiService.put(
            `project/${project}/questionnaire/${questionnaire}`,
            params
        );
    },
    destroy(project, questionnaire) {
        return ApiService.delete(
            `project/${project}/questionnaire/${questionnaire}`
        );
    },
};
