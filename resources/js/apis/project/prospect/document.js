import ApiService from "@/apis/api.service";

export default {
    show(project, prospect, document, params) {
        return ApiService.get(
            `project/${project}/import/${prospect}/document/${document}`
        );
    },
};
