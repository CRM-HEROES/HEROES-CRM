import ApiService from "@/apis/api.service";

export default {
    get(project, role, params) {
        return ApiService.get(
            `project/${project}/role/${role}/calendar`,
            params
        );
    },
    update(project, role, calendar) {
        return ApiService.put(
            `project/${project}/role/${role}/calendar/${calendar}`
        );
    },
    destroy(project, role, calendar) {
        return ApiService.delete(
            `project/${project}/role/${role}/calendar/${calendar}`
        );
    },
    bulkUpdate(project, roles, calendars) {
        return ApiService.put(`project/${project}/role/calendar/bulk`, {
            action: "attach",
            roles: roles,
            calendars: calendars,
        });
    },
    bulkDestroy(project, roles, calendars) {
        return ApiService.put(`project/${project}/role/calendar/bulk`, {
            action: "detach",
            roles: roles,
            calendars: calendars,
        });
    },
};
