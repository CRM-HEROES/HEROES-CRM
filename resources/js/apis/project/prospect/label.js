import ApiService from "@/apis/api.service";

export default {
    get(project, prospect, params) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/label`,
            params
        );
    },
    update(project, prospect, label) {
        return ApiService.put(
            `project/${project}/prospect/${prospect}/label/${label}`
        );
    },
    destroy(project, prospect, label) {
        return ApiService.delete(
            `project/${project}/prospect/${prospect}/label/${label}`
        );
    },
    bulkUpdate(project, prospects, labels) {
        return ApiService.put(`project/${project}/prospect/label/bulk`, {
            action: "attach",
            prospects: prospects,
            labels: labels,
        });
    },
    bulkDestroy(project, prospects, labels) {
        return ApiService.put(`project/${project}/prospect/label/bulk`, {
            action: "detach",
            prospects: prospects,
            labels: labels,
        });
    },
};
