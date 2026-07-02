import ApiService from "@/apis/api.service";

export default {
    show(project, user, setting) {
        return ApiService.get(
            `project/${project}/user/${user}/setting/${setting}`
        );
    },
    update(project, user, setting, params) {
        return ApiService.put(
            `project/${project}/user/${user}/setting/${setting}`,
            {
                value: params,
            }
        );
    },
    destroy(project, user, setting) {
        return ApiService.delete(
            `project/${project}/user/${user}/setting/${setting}`
        );
    },
};
