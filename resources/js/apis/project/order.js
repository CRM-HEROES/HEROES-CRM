import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/order`, params);
    },
    bulkDestroy(project, orders) {
        return ApiService.put(`project/${project}/order/bulk`, {
            action: "delete",
            orders: orders,
        });
    },
};
