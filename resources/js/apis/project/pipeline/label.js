import ApiService from "@/apis/api.service";

export default {
    get(project, pipeline, params) {
        return ApiService.get(
            `project/${project}/pipeline/${pipeline}/label`,
            params
        );
    },
    update(project, pipeline, label) {
        return ApiService.put(
            `project/${project}/pipeline/${pipeline}/label/${label}`
        );
    },
    destroy(project, pipeline, label) {
        return ApiService.delete(
            `project/${project}/pipeline/${pipeline}/label/${label}`
        );
    },
    orders(project, pipeline, params) {
        return ApiService.put(
            `project/${project}/pipeline/${pipeline}/label/orders`, params
        );
    },
    bulkUpdate(project, pipelines, labels) {
        return ApiService.put(`project/${project}/pipeline/label/bulk`, {
            action: "attach",
            pipelines: pipelines,
            labels: labels,
        });
    },
    bulkDestroy(project, pipelines, labels) {
        return ApiService.put(`project/${project}/pipeline/label/bulk`, {
            action: "detach",
            pipelines: pipelines,
            labels: labels,
        });
    },
};
