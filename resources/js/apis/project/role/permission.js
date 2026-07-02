import ApiService from "@/apis/api.service";

export default {
    get(project, role, params) {
        return ApiService.get(
            `project/${project}/role/${role}/permission`,
            params
        );
    },
    update(project, role, permission) {
        return ApiService.put(
            `project/${project}/role/${role}/permission/${permission}`
        );
    },
    destroy(project, role, permission) {
        return ApiService.delete(
            `project/${project}/role/${role}/permission/${permission}`
        );
    },
};
