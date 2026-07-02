import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/event`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/event`, params);
    },
    show(project, event) {
        return ApiService.get(`project/${project}/event/${event}`);
    },
    update(project, event, params) {
        return ApiService.put(`project/${project}/event/${event}`, params);
    },
    destroy(project, event) {
        return ApiService.delete(`project/${project}/event/${event}`);
    },
    dailyDirection(/*project, */ user, date) {
        return ApiService.get(`user/${user.id}/events-daily-direction/${date}`);
    },
    addUser(project, event, user) {
        return ApiService.put(`project/${project}/user/${user}/event/${event}`);
    },
    removeUser(project, event, user) {
        return ApiService.delete(
            `project/${project}/user/${user}/event/${event}`
        );
    },
};
