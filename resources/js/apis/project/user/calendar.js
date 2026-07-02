import ApiService from "@/apis/api.service";

export default {
    get(project, user, params) {
        return ApiService.get(
            `project/${project}/user/${user}/calendar`,
            params
        );
    },
    users(project, calendar) {
        return ApiService.get(`project/${project}/calendar/${calendar}/user`);
    },
    update(project, user, calendar) {
        return ApiService.put(
            `project/${project}/user/${user}/calendar/${calendar}`
        );
    },
    destroy(project, user, calendar) {
        return ApiService.delete(
            `project/${project}/user/${user}/calendar/${calendar}`
        );
    },
    bulkUpdate(project, users, calendars) {
        return ApiService.put(`project/${project}/user/calendar/bulk`, {
            action: "attach",
            users: users,
            calendars: calendars,
        });
    },
    bulkDestroy(project, users, calendars) {
        return ApiService.put(`project/${project}/user/calendar/bulk`, {
            action: "detach",
            users: users,
            calendars: calendars,
        });
    },
};
