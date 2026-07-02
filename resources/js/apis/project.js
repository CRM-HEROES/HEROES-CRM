import ApiService from "@/apis/api.service";

export default {
    get(params) {
        return ApiService.get("project", params);
    },
    create(params) {
        return ApiService.post("project", params);
    },
    show(slug) {
        return ApiService.get(`project/${slug}`);
    },
    update(slug, params) {
        return ApiService.put(`project/${slug}`, params);
    },
    destroy(slug) {
        return ApiService.delete(`project/${slug}`);
    },
    orders(params) {
        return ApiService.put(`project/orders`, params);
    },
    bulkDestroy(projects) {
        return ApiService.put(`project/bulk`, {
            action: "delete",
            projects: projects,
        });
    },
};
