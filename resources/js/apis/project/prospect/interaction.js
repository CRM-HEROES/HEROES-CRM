import ApiService from "@/apis/api.service";

export default {
    get(project, prospect, params) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/interaction`,
            params
        );
    },
    create(project, prospect, params) {
        return ApiService.post(
            `project/${project}/prospect/${prospect}/interaction`,
            params
        );
    },
    show(project, prospect, interaction) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/interaction/${interaction}`
        );
    },
    update(project, prospect, interaction, params) {
        return ApiService.put(
            `project/${project}/prospect/${prospect}/interaction/${interaction}`,
            params
        );
    },
    destroy(project, prospect, interaction) {
        return ApiService.delete(
            `project/${project}/prospect/${prospect}/interaction/${interaction}`
        );
    },
};
