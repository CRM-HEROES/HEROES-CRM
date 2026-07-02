import ApiService from "@/apis/api.service";

export default {
    get(project, prospect, folder, params) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/folder/${folder}/file`,
            params
        );
    },
    create(project, prospect, folder, params, settings) {
        return ApiService.post(
            `project/${project}/prospect/${prospect}/folder/${folder}/file`,
            params,
            settings
        );
    },
    show(project, prospect, folder, file) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/folder/${folder}/file/${file}`
        );
    },
    users(project, prospect, folder, file) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/folder/${folder}/file/${file}/user`
        );
    },
    bulkUpdate(project, prospect, folder, users, files) {
        return ApiService.put(`project/${project}/prospect/${prospect}/folder/${folder}/user/file/bulk`, {
            action: "attach",
            users: users,
            files: files,
        });
    },
    bulkDestroy(project, prospect, folder, users, files) {
        return ApiService.put(`project/${project}/prospect/${prospect}/folder/${folder}/user/file/bulk`, {
            action: "detach",
            users: users,
            files: files,
        });
    },
    destroy(project, prospect, folder, file) {
        return ApiService.delete(
            `project/${project}/prospect/${prospect}/folder/${folder}/file/${file}`
        );
    },
};
