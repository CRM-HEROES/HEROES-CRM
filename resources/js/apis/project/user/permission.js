import ApiService from "@/apis/api.service";

export default {
    get(project, user, params) {
        return ApiService.get(
            `project/${project}/user/${user}/permission`,
            params
        );
    },
    update(project, user, permission) {
        return ApiService.put(
            `project/${project}/user/${user}/permission/${permission}`
        );
    },
    destroy(project, user, permission) {
        return ApiService.delete(
            `project/${project}/user/${user}/permission/${permission}`
        );
    },
};
