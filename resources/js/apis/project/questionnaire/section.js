import ApiService from "@/apis/api.service";

export default {
    get(project, questionnaire, params) {
        return ApiService.get(
            `project/${project}/questionnaire/${questionnaire}/section`,
            params
        );
    },
    create(project, questionnaire, params) {
        return ApiService.post(
            `project/${project}/questionnaire/${questionnaire}/section`,
            params
        );
    },
    show(project, questionnaire, section) {
        return ApiService.get(
            `project/${project}/questionnaire/${questionnaire}/section/${section}`
        );
    },
    update(project, questionnaire, section, params) {
        return ApiService.put(
            `project/${project}/questionnaire/${questionnaire}/section/${section}`,
            params
        );
    },
    destroy(project, questionnaire, section) {
        return ApiService.delete(
            `project/${project}/questionnaire/${questionnaire}/section/${section}`
        );
    },
};
