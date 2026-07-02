import ApiService from "@/apis/api.service";

export default {
    get(project, projectImport) {
        return ApiService.get(
            `project/${project}/import/${projectImport}/group`
        );
    },
    update(project, projectImport, group) {
        return ApiService.put(
            `project/${project}/import/${projectImport}/group/${group}`
        );
    },
    destroy(project, projectImport, group) {
        return ApiService.delete(
            `project/${project}/import/${projectImport}/group/${group}`
        );
    },
};
