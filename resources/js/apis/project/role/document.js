import ApiService from "@/apis/api.service";

export default {
    get(project, role, params) {
        return ApiService.get(
            `project/${project}/role/${role}/document`,
            params
        );
    },
    update(project, role, document) {
        return ApiService.put(
            `project/${project}/role/${role}/document/${document}`
        );
    },
    destroy(project, role, document) {
        return ApiService.delete(
            `project/${project}/role/${role}/document/${document}`
        );
    },
    bulkUpdate(project, roles, documents) {
        return ApiService.put(`project/${project}/role/document/bulk`, {
            action: "attach",
            roles: roles,
            documents: documents,
        });
    },
    bulkDestroy(project, roles, documents) {
        return ApiService.put(`project/${project}/role/document/bulk`, {
            action: "detach",
            roles: roles,
            documents: documents,
        });
    },
};
