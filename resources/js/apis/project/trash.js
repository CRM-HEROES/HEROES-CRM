import ApiService from "@/apis/api.service";

export default {
    prospect(project) {
        return ApiService.get(`project/${project}/trash/prospect`);
    },
    category(project) {
        return ApiService.get(`project/${project}/trash/category`);
    },
    label(project) {
        return ApiService.get(`project/${project}/trash/label`);
    },
    thread(project) {
        return ApiService.get(`project/${project}/trash/thread`);
    },
    message(project) {
        return ApiService.get(`project/${project}/trash/message`);
    },
    folder(project) {
        return ApiService.get(`project/${project}/trash/folder`);
    },
    file(project) {
        return ApiService.get(`project/${project}/trash/file`);
    },
    orderStatus(project) {
        return ApiService.get(`project/${project}/trash/order-status`);
    },
    orderStep(project) {
        return ApiService.get(`project/${project}/trash/order-step`);
    },
    orderAction(project) {
        return ApiService.get(`project/${project}/trash/order-action`);
    },
    product(project) {
        return ApiService.get(`project/${project}/trash/product`);
    },
    order(project) {
        return ApiService.get(`project/${project}/trash/order`);
    },
    calendar(project) {
        return ApiService.get(`project/${project}/trash/calendar`);
    },
    event(project) {
        return ApiService.get(`project/${project}/trash/event`);
    },

    bulkDestroy(project, items) {
        return ApiService.put(`project/${project}/trash/bulk`, {
            action: "delete",
            ...items,
        });
    },
    bulkRestore(project, items) {
        return ApiService.put(`project/${project}/trash/bulk`, {
            action: "restore",
            ...items,
        });
    },
};
