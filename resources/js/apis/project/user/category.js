import ApiService from "@/apis/api.service";

export default {
    get(project, user, params) {
        return ApiService.get(
            `project/${project}/user/${user}/category`,
            params
        );
    },
    users(project, category) {
        return ApiService.get(`project/${project}/category/${category}/user`);
    },
    update(project, user, category) {
        return ApiService.put(
            `project/${project}/user/${user}/category/${category}`
        );
    },
    destroy(project, user, category) {
        return ApiService.delete(
            `project/${project}/user/${user}/category/${category}`
        );
    },
    bulkUpdate(project, users, categories) {
        return ApiService.put(`project/${project}/user/category/bulk`, {
            action: "attach",
            users: users,
            categories: categories,
        });
    },
    bulkDestroy(project, users, categories) {
        return ApiService.put(`project/${project}/user/category/bulk`, {
            action: "detach",
            users: users,
            categories: categories,
        });
    },
};
