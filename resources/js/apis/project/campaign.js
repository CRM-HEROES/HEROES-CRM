import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/campaign`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/campaign`, params);
    },
    show(project, campaign) {
        return ApiService.get(`project/${project}/campaign/${campaign}`);
    },
    update(project, campaign, params) {
        return ApiService.put(
            `project/${project}/campaign/${campaign}`,
            params
        );
    },
    destroy(project, campaign) {
        return ApiService.delete(`project/${project}/campaign/${campaign}`);
    },
    addRule(project, campaign, rule) {
        return ApiService.put(
            `project/${project}/campaign/${campaign}/rule/${rule}`
        );
    },
    removeRule(project, campaign, rule) {
        return ApiService.delete(
            `project/${project}/campaign/${campaign}/rule/${rule}`
        );
    },
    checkRules(project, campaign) {
        return ApiService.delete(
            `project/${project}/campaign/${campaign}/check-rules`
        );
    },
    addOperator(project, campaign, operator) {
        return ApiService.put(
            `project/${project}/campaign/${campaign}/operator/${operator}`
        );
    },
    removeOperator(project, campaign, operator) {
        return ApiService.delete(
            `project/${project}/campaign/${campaign}/operator/${operator}`
        );
    },
    addAction(project, campaign, action) {
        return ApiService.put(
            `project/${project}/campaign/${campaign}/action/${action}`
        );
    },
    removeAction(project, campaign, action) {
        return ApiService.delete(
            `project/${project}/campaign/${campaign}/action/${action}`
        );
    },
};
