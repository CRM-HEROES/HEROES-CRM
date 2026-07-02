import ApiService from "@/apis/api.service";

export default {
    get(project, user, folder, params) {
        return ApiService.get(
            `project/${project}/user/${user}/folder/${folder}/file`,
            params
        );
    },
    create(project, user, folder, params, settings) {
        return ApiService.post(
            `project/${project}/user/${user}/folder/${folder}/file`,
            params,
            settings
        );
    },
    show(project, user, folder, file) {
        return ApiService.get(
            `project/${project}/user/${user}/folder/${folder}/file/${file}`
        );
    },
    destroy(project, user, folder, file) {
        return ApiService.delete(
            `project/${project}/user/${user}/folder/${folder}/file/${file}`
        );
    },
};
