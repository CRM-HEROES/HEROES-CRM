import ApiService from "@/apis/api.service";

export default {
    get(project, user, params) {
        return ApiService.get(
            `project/${project}/user/${user}/document`,
            params
        );
    },
    update(project, user, document) {
        return ApiService.put(
            `project/${project}/user/${user}/document/${document}`
        );
    },
    destroy(project, user, document) {
        return ApiService.delete(
            `project/${project}/user/${user}/document/${document}`
        );
    },
    bulkUpdate(project, users, documents) {
        return ApiService.put(`project/${project}/user/document/bulk`, {
            action: "attach",
            users: users,
            documents: documents,
        });
    },
    bulkDestroy(project, users, documents) {
        return ApiService.put(`project/${project}/user/document/bulk`, {
            action: "detach",
            users: users,
            documents: documents,
        });
    },
};
