import ApiService from "@/apis/api.service";

export default {
    users(project) {
        return ApiService.get(`project/${project}/attendance/users`);
    },
    get(project, from, to) {
        return ApiService.get(`project/${project}/attendance`, {
            params: { from, to },
        });
    },
    store(project, params) {
        return ApiService.post(`project/${project}/attendance`, params);
    },
    leave(project, params) {
        return ApiService.post(`project/${project}/attendance/leave`, params);
    },
    removeLeave(project, params) {
        return ApiService.post(`project/${project}/attendance/leave/remove`, params);
    },
    update(project, id, params) {
        return ApiService.put(`project/${project}/attendance/${id}`, params);
    },
    destroy(project, id) {
        return ApiService.delete(`project/${project}/attendance/${id}`);
    },
};
