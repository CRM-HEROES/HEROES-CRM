import ApiService from "@/apis/api.service";

export default {
    get(params) {
        return ApiService.get(`stat-chart`, params);
    },
    orders(params) {
        return ApiService.put(`stat-chart/orders`, params);
    },
    create(project, params) {
        return project
            ? ApiService.post(`project/${project}/stat-chart`, params)
            : ApiService.post(`stat-chart`, params);
    },
    show(project, metric, params) {
        return ApiService.get(
            `project/${project}/stat-chart/${metric}`,
            params
        );
    },
    update(project, metric, params) {
        params.options = params.options ? { ...params.options } : {};
        return ApiService.put(
            `project/${project}/stat-chart/${metric}`,
            params
        );
    },
    destroy(project, metric) {
        return ApiService.delete(`project/${project}/stat-chart/${metric}`);
    },
};
