import ApiService from "@/apis/api.service";

export default {
    get(project, user, params) {
        return ApiService.get(`project/${project}/user/${user}/menu`, params);
    },
    users(project, menu) {
        return ApiService.get(`project/${project}/menu/${menu}/user`);
    },
    update(project, user, menu, params) {
        return ApiService.put(
            `project/${project}/user/${user}/menu/${menu}`,
            params
        );
    },
    destroy(project, user, menu) {
        return ApiService.delete(
            `project/${project}/user/${user}/menu/${menu}`
        );
    },
    bulkUpdate(project, users, menus) {
        return ApiService.put(`project/${project}/user/menu/bulk`, {
            action: "attach",
            users: users,
            menus: menus,
        });
    },
    bulkDestroy(project, users, menus) {
        return ApiService.put(`project/${project}/user/menu/bulk`, {
            action: "detach",
            users: users,
            menus: menus,
        });
    },
};
