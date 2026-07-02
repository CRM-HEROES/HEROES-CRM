import ApiService from "@/apis/api.service";

export default {
    get(project, prospect, pipeline, params) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/pipeline/${pipeline}/label`,
            params
        );
    },
    update(project, prospect, pipeline, label) {
        return ApiService.put(
            `project/${project}/prospect/${prospect}/pipeline/${pipeline}/label/${label}`
        );
    },
};
