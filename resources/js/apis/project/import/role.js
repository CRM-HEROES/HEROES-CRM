import ApiService from "@/apis/api.service";

export default {
    get(project, projectImport) {
        return ApiService.get(
            `project/${project}/import/${projectImport}/role`
        );
    },
    update(project, projectImport, role) {
        return ApiService.put(
            `project/${project}/import/${projectImport}/role/${role}`
        );
    },
    destroy(project, projectImport, role) {
        return ApiService.delete(
            `project/${project}/import/${projectImport}/role/${role}`
        );
    },
};
