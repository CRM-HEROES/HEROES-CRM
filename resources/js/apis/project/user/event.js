import ApiService from "@/apis/api.service";

export default {
    update(project, user, event) {
        return ApiService.put(`project/${project}/user/${user}/event/${event}`);
    },
    destroy(project, user, event) {
        return ApiService.delete(
            `project/${project}/user/${user}/event/${event}`
        );
    },
};
