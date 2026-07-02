import ApiService from "@/apis/api.service";

export default {
    get(project, role, params) {
        return ApiService.get(
            `project/${project}/role/${role}/category`,
            params
        );
    },
    update(project, role, category) {
        return ApiService.put(
            `project/${project}/role/${role}/category/${category}`
        );
    },
    destroy(project, role, category) {
        return ApiService.delete(
            `project/${project}/role/${role}/category/${category}`
        );
    },
    bulkUpdate(project, roles, categories) {
        return ApiService.put(`project/${project}/role/category/bulk`, {
            action: "attach",
            roles: roles,
            categories: categories,
        });
    },
    bulkDestroy(project, roles, categories) {
        return ApiService.put(`project/${project}/role/category/bulk`, {
            action: "detach",
            roles: roles,
            categories: categories,
        });
    },
};
