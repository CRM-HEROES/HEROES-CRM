import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/prospect`, params);
    },
    count(project, params) {
        return ApiService.get(`project/${project}/prospect/count`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/prospect`, params);
    },
    show(project, prospect) {
        return ApiService.get(`project/${project}/prospect/${prospect}`);
    },
    duplicate(project, prospect) {
        return ApiService.post(
            `project/${project}/prospect/${prospect}/duplicate`
        );
    },
    update(project, prospect, params) {
        return ApiService.put(
            `project/${project}/prospect/${prospect}`,
            params
        );
    },
    destroy(project, prospect) {
        return ApiService.delete(`project/${project}/prospect/${prospect}`);
    },
    bulkDestroy(project, prospects) {
        return ApiService.put(`project/${project}/prospect/bulk`, {
            action: "delete",
            prospects: prospects,
        });
    },
    bulkForceDestroy(project, prospects) {
        return ApiService.put(`project/${project}/prospect/bulk`, {
            action: "force_delete",
            prospects: prospects,
        });
    },
    bulkRestore(project, prospects) {
        return ApiService.put(`project/${project}/prospect/bulk`, {
            action: "restore",
            prospects: prospects,
        });
    },
    bulkProcessed(project, prospects) {
        return ApiService.put(`project/${project}/prospect/bulk`, {
            action: "processed",
            prospects: prospects,
        });
    },
    bulkNotProcessed(project, prospects) {
        return ApiService.put(`project/${project}/prospect/bulk`, {
            action: "not_processed",
            prospects: prospects,
        });
    },
    bulkUpdate(project, prospects, field, value) {
        return ApiService.put(`project/${project}/prospect/bulk`, {
            action: "field",
            prospects: prospects,
            field: field,
            value: value,
        });
    },
    bulkProjectProspect(project, prospects, toProject, mapping) {
        return ApiService.put(`project/${project}/prospect/bulk`, {
            action: "project",
            prospects: prospects,
            project: toProject,
            mapping,
        });
    },
};
