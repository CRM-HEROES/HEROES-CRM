import ApiService from "@/apis/api.service";

export default {
    get(project, projectImport) {
        return ApiService.get(
            `project/${project}/import/${projectImport}/ai-agent`
        );
    },
    update(project, projectImport, agent) {
        return ApiService.put(
            `project/${project}/import/${projectImport}/ai-agent/${agent}`
        );
    },
    destroy(project, projectImport, agent) {
        return ApiService.delete(
            `project/${project}/import/${projectImport}/ai-agent/${agent}`
        );
    },
};
