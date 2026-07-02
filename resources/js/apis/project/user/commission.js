import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/commission`, params);
    },
    update(project, user, product, action, params) {
        return ApiService.put(
            `project/${project}/user/${user}/product/${product}/action/${action}/commission`,
            params
        );
    },
    destroy(project, user, product, action) {
        return ApiService.delete(
            `project/${project}/user/${user}/product/${product}/action/${action}/commission`
        );
    },
    bulkUpdate(project, params) {
        return ApiService.put(`project/${project}/commission/bulk`, params);
    },
};
