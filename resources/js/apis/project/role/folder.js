import ApiService from "@/apis/api.service";

export default {
    get(project, role, params) {
        return ApiService.get(`project/${project}/role/${role}/folder`, params);
    },
    update(project, role, folder) {
        return ApiService.put(
            `project/${project}/role/${role}/folder/${folder}`
        );
    },
    destroy(project, role, folder) {
        return ApiService.delete(
            `project/${project}/role/${role}/folder/${folder}`
        );
    },
    bulkUpdate(project, roles, folders) {
        return ApiService.put(`project/${project}/role/folder/bulk`, {
            action: "attach",
            roles: roles,
            folders: folders,
        });
    },
    bulkDestroy(project, roles, folders) {
        return ApiService.put(`project/${project}/role/folder/bulk`, {
            action: "detach",
            roles: roles,
            folders: folders,
        });
    },
};
