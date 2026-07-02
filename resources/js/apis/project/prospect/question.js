import ApiService from "@/apis/api.service";

export default {
    get(project, prospect, params) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/question`,
            params
        );
    },
    create(project, prospect, question, params) {
        return ApiService.post(
            `project/${project}/prospect/${prospect}/question/${question}`,
            params
        );
    },
    destroy(project, prospect, question) {
        return ApiService.delete(
            `project/${project}/prospect/${prospect}/question/${question}`
        );
    },
};
