import ApiService from "@/apis/api.service";

export default {
    get(project, prospect, order, params) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/order/${order}/product`,
            params
        );
    },
    update(project, prospect, order, product, params) {
        return ApiService.put(
            `project/${project}/prospect/${prospect}/order/${order}/product/${product}`,
            params
        );
    },
    destroy(project, prospect, order, product) {
        return ApiService.delete(
            `project/${project}/prospect/${prospect}/order/${order}/product/${product}`
        );
    },
};
