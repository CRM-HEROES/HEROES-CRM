import ApiService from "@/apis/api.service";

export default {
    lock(project, user) {
        return ApiService.put(`project/${project}/user/${user}/lock`);
    },
    unlock(project, user) {
        return ApiService.delete(`project/${project}/user/${user}/lock`);
    },
};
