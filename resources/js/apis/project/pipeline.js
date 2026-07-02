import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/pipeline`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/pipeline`, params);
    },
    show(project, pipeline) {
        return ApiService.get(`project/${project}/pipeline/${pipeline}`);
    },
    update(project, pipeline, params) {
        return ApiService.put(
            `project/${project}/pipeline/${pipeline}`,
            params
        );
    },
    destroy(project, pipeline) {
        return ApiService.delete(`project/${project}/pipeline/${pipeline}`);
    },
    count(project, pipeline) {
        return ApiService.get(`project/${project}/pipeline/${pipeline}/count`);
    },
};
