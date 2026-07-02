import ApiService from "@/apis/api.service";

export default {
    get(project, questionnaire, section, params) {
        return ApiService.get(
            `project/${project}/questionnaire/${questionnaire}/section/${section}/question`,
            params
        );
    },
    create(project, questionnaire, section, params) {
        return ApiService.post(
            `project/${project}/questionnaire/${questionnaire}/section/${section}/question`,
            params
        );
    },
    show(project, questionnaire, section, question) {
        return ApiService.get(
            `project/${project}/questionnaire/${questionnaire}/section/${section}/question/${question}`
        );
    },
    update(project, questionnaire, section, question, params) {
        return ApiService.put(
            `project/${project}/questionnaire/${questionnaire}/section/${section}/question/${question}`,
            params
        );
    },
    destroy(project, questionnaire, section, question) {
        return ApiService.delete(
            `project/${project}/questionnaire/${questionnaire}/section/${section}/question/${question}`
        );
    },
};
