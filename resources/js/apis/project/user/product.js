import ApiService from "@/apis/api.service";

export default {
    get(project, user, params) {
        return ApiService.get(
            `project/${project}/user/${user}/product`,
            params
        );
    },
    users(project, product) {
        return ApiService.get(`project/${project}/product/${product}/user`);
    },
    update(project, user, product) {
        return ApiService.put(
            `project/${project}/user/${user}/product/${product}`
        );
    },
    destroy(project, user, product) {
        return ApiService.delete(
            `project/${project}/user/${user}/product/${product}`
        );
    },
    bulkUpdate(project, users, products) {
        return ApiService.put(`project/${project}/user/product/bulk`, {
            action: "attach",
            users: users,
            products: products,
        });
    },
    bulkDestroy(project, users, products) {
        return ApiService.put(`project/${project}/user/product/bulk`, {
            action: "detach",
            users: users,
            products: products,
        });
    },
};
