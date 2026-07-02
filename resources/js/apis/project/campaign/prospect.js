import ApiService from "@/apis/api.service";

export default {
    get(project, campaign, params) {
        return ApiService.get(
            `project/${project}/campaign/${campaign}/prospect`,
            params
        );
    },
    matching(project, campaign, params) {
        return ApiService.get(
            `project/${project}/campaign/${campaign}/prospect/matching`,
            params
        );
    },
};
