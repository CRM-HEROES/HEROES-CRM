import ApiService from "@/apis/api.service";

export default {
    get(project, projectImport) {
        return ApiService.get(
            `project/${project}/import/${projectImport}/user`
        );
    },
    update(project, projectImport, user) {
        return ApiService.put(
            `project/${project}/import/${projectImport}/user/${user}`
        );
    },
    destroy(project, projectImport, user) {
        return ApiService.delete(
            `project/${project}/import/${projectImport}/user/${user}`
        );
    },
};
