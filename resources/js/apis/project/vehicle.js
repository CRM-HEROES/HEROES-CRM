import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/vehicle`, params);
    },
    store(project, params) {
        return ApiService.post(`project/${project}/vehicle`, params);
    },
    show(project, vehicle) {
        return ApiService.get(`project/${project}/vehicle/${vehicle}`);
    },
    update(project, vehicle, params) {
        return ApiService.put(`project/${project}/vehicle/${vehicle}`, params);
    },
    destroy(project, vehicle) {
        return ApiService.delete(`project/${project}/vehicle/${vehicle}`);
    },
    positions(project, vehicle) {
        return ApiService.get(
            `project/${project}/vehicle/${vehicle}/positions`
        );
    },
};
