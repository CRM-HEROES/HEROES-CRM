import ApiService from "@/apis/api.service";

export default {
    get(project, prospect, params) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/group`,
            params
        );
    },
    update(project, prospect, group) {
        return ApiService.put(
            `project/${project}/prospect/${prospect}/group/${group}`
        );
    },
    destroy(project, prospect, group) {
        return ApiService.delete(
            `project/${project}/prospect/${prospect}/group/${group}`
        );
    },
    bulkUpdate(project, prospects, groups) {
        return ApiService.put(`project/${project}/prospect/group/bulk`, {
            action: "attach",
            prospects: prospects,
            groups: groups,
        });
    },
    bulkDestroy(project, prospects, groups) {
        return ApiService.put(`project/${project}/prospect/group/bulk`, {
            action: "detach",
            prospects: prospects,
            groups: groups,
        });
    },
};
