import ApiService from "@/apis/api.service";

export default {
    get(project, prospect, params) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/questionnaire`,
            params
        );
    },
    show(project, prospect, questionnaire) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/questionnaire/${questionnaire}`
        );
    },
    update(project, prospect, questionnaire) {
        return ApiService.put(
            `project/${project}/prospect/${prospect}/questionnaire/${questionnaire}`
        );
    },
    destroy(project, prospect, questionnaire) {
        return ApiService.delete(
            `project/${project}/prospect/${prospect}/questionnaire/${questionnaire}`
        );
    },
};
