import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/category`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/category`, params);
    },
    show(project, category) {
        return ApiService.get(`project/${project}/category/${category}`);
    },
    update(project, category, params) {
        return ApiService.put(
            `project/${project}/category/${category}`,
            params
        );
    },
    destroy(project, category) {
        return ApiService.delete(`project/${project}/category/${category}`);
    },
    field(project, category) {
        return ApiService.put(`project/${project}/category/${category}/field`);
    },
    combine(project, category1, category2) {
        return ApiService.put(
            `project/${project}/category/${category1}/combine/${category2}`
        );
    },
    orders(project, params) {
        return ApiService.put(`project/${project}/category/orders`, params);
    },
};
