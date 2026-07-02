import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/calendar`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/calendar`, params);
    },
    show(project, calendar) {
        return ApiService.get(`project/${project}/calendar/${calendar}`);
    },
    update(project, calendar, params) {
        return ApiService.put(
            `project/${project}/calendar/${calendar}`,
            params
        );
    },
    destroy(project, calendar, params) {
        return ApiService.delete(`project/${project}/calendar/${calendar}`, {
            data: params,
        });
    },
};
