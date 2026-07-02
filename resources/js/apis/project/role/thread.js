import ApiService from "@/apis/api.service";

export default {
    get(project, role, params) {
        return ApiService.get(`project/${project}/role/${role}/thread`, params);
    },
    update(project, role, thread) {
        return ApiService.put(
            `project/${project}/role/${role}/thread/${thread}`
        );
    },
    destroy(project, role, thread) {
        return ApiService.delete(
            `project/${project}/role/${role}/thread/${thread}`
        );
    },
    bulkUpdate(project, roles, threads) {
        return ApiService.put(`project/${project}/role/thread/bulk`, {
            action: "attach",
            roles: roles,
            threads: threads,
        });
    },
    bulkDestroy(project, roles, threads) {
        return ApiService.put(`project/${project}/role/thread/bulk`, {
            action: "detach",
            roles: roles,
            threads: threads,
        });
    },
};
