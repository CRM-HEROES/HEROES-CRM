import ApiService from "@/apis/api.service";

export default {
    get(project, user, params) {
        return ApiService.get(
            `project/${project}/user/${user}/questionnaire`,
            params
        );
    },
    update(project, user, questionnaire) {
        return ApiService.put(
            `project/${project}/user/${user}/questionnaire/${questionnaire}`
        );
    },
    destroy(project, user, questionnaire) {
        return ApiService.delete(
            `project/${project}/user/${user}/questionnaire/${questionnaire}`
        );
    },
    bulkUpdate(project, users, questionnaires) {
        return ApiService.put(`project/${project}/user/questionnaire/bulk`, {
            action: "attach",
            users: users,
            questionnaires: questionnaires,
        });
    },
    bulkDestroy(project, users, questionnaires) {
        return ApiService.put(`project/${project}/user/questionnaire/bulk`, {
            action: "detach",
            users: users,
            questionnaires: questionnaires,
        });
    },
};
