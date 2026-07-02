import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/campaign-operator`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/campaign-operator`, params);
    },
    show(project, operator) {
        return ApiService.get(
            `project/${project}/campaign-operator/${operator}`
        );
    },
    update(project, operator, params) {
        return ApiService.put(
            `project/${project}/campaign-operator/${operator}`,
            params
        );
    },
    destroy(project, operator) {
        return ApiService.delete(
            `project/${project}/campaign-operator/${operator}`
        );
    },
    addRule(project, operator, rule) {
        return ApiService.put(
            `project/${project}/campaign-operator/${operator}/rule/${rule}`
        );
    },
    removeRule(project, operator, rule) {
        return ApiService.delete(
            `project/${project}/campaign-operator/${operator}/rule/${rule}`
        );
    },
    addOperator(project, parent, child) {
        return ApiService.put(
            `project/${project}/campaign-operator/${parent}/operator/${child}`
        );
    },
    removeOperator(project, parent, child) {
        return ApiService.delete(
            `project/${project}/campaign-operator/${parent}/operator/${child}`
        );
    },
};
