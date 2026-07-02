import ApiService from "@/apis/api.service";

export default {
    get(project, projectImport) {
        return ApiService.get(
            `project/${project}/import/${projectImport}/label`
        );
    },
    update(project, projectImport, label) {
        return ApiService.put(
            `project/${project}/import/${projectImport}/label/${label}`
        );
    },
    destroy(project, projectImport, label) {
        return ApiService.delete(
            `project/${project}/import/${projectImport}/label/${label}`
        );
    },
};
