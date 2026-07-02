import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/product`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/product`, params);
    },
    show(project, product) {
        return ApiService.get(`project/${project}/product/${product}`);
    },
    update(project, product, params) {
        return ApiService.put(`project/${project}/product/${product}`, params);
    },
    destroy(project, product) {
        return ApiService.delete(`project/${project}/product/${product}`);
    },
    addImage(project, product, params, settings) {
        return ApiService.post(
            `project/${project}/product/${product}/image`,
            params,
            settings
        );
    },
    removeImage(project, product, image) {
        return ApiService.delete(
            `project/${project}/product/${product}/image/${image}`
        );
    },
    updateImage(project, product, image, params) {
        return ApiService.put(
            `project/${project}/product/${product}/image/${image}`,
            params
        );
    },
};
