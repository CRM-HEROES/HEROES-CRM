import ApiService from "@/apis/api.service";

export default {
    duplicate(project, role, params) {
        return ApiService.put(
            `project/${project}/role/${role}/duplicate`,
            params
        );
    },
};
