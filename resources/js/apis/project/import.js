import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/import`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/import`, params);
    },
    show(project, projectImport) {
        return ApiService.get(`project/${project}/import/${projectImport}`);
    },
    update(project, projectImport, params) {
        return ApiService.put(
            `project/${project}/import/${projectImport}`,
            params
        );
    },
    destroy(project, projectImport, params) {
        return ApiService.delete(`project/${project}/import/${projectImport}`, {
            data: params,
        });
    },
};
