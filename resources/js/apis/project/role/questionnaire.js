import ApiService from "@/apis/api.service";

export default {
    get(project, role, params) {
        return ApiService.get(
            `project/${project}/role/${role}/questionnaire`,
            params
        );
    },
    update(project, role, questionnaire) {
        return ApiService.put(
            `project/${project}/role/${role}/questionnaire/${questionnaire}`
        );
    },
    destroy(project, role, questionnaire) {
        return ApiService.delete(
            `project/${project}/role/${role}/questionnaire/${questionnaire}`
        );
    },
    bulkUpdate(project, roles, questionnaires) {
        return ApiService.put(`project/${project}/role/questionnaire/bulk`, {
            action: "attach",
            roles: roles,
            questionnaires: questionnaires,
        });
    },
    bulkDestroy(project, roles, questionnaires) {
        return ApiService.put(`project/${project}/role/questionnaire/bulk`, {
            action: "detach",
            roles: roles,
            questionnaires: questionnaires,
        });
    },
};
