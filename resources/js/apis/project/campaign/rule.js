import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/campaign-rule`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/campaign-rule`, params);
    },
    show(project, rule) {
        return ApiService.get(`project/${project}/campaign-rule/${rule}`);
    },
    update(project, rule, params) {
        return ApiService.put(
            `project/${project}/campaign-rule/${rule}`,
            params
        );
    },
    destroy(project, rule) {
        return ApiService.delete(`project/${project}/campaign-rule/${rule}`);
    },
};
