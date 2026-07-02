import ApiService from "@/apis/api.service";

export default {
    get(project, questionnaire, section, question, params) {
        return ApiService.get(
            `project/${project}/questionnaire/${questionnaire}/section/${section}/question/${question}/option`,
            params
        );
    },
    create(project, questionnaire, section, question, params) {
        return ApiService.post(
            `project/${project}/questionnaire/${questionnaire}/section/${section}/question/${question}/option`,
            params
        );
    },
    show(project, questionnaire, section, question, option) {
        return ApiService.get(
            `project/${project}/questionnaire/${questionnaire}/section/${section}/question/${question}/option/${option}`
        );
    },
    update(project, questionnaire, section, question, option, params) {
        return ApiService.put(
            `project/${project}/questionnaire/${questionnaire}/section/${section}/question/${question}/option/${option}`,
            params
        );
    },
    destroy(project, questionnaire, section, question, option) {
        return ApiService.delete(
            `project/${project}/questionnaire/${questionnaire}/section/${section}/question/${question}/option/${option}`
        );
    },
};
