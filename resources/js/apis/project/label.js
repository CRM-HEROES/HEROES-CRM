import ApiService from "@/apis/api.service";

export default {
    get(project, category, params) {
        return ApiService.get(
            `project/${project}/category/${category}/label`,
            params
        );
    },
    create(project, category, params) {
        return ApiService.post(
            `project/${project}/category/${category}/label`,
            params
        );
    },
    show(project, category, label) {
        return ApiService.get(
            `project/${project}/category/${category}/label/${label}`
        );
    },
    update(project, category, label, params) {
        return ApiService.put(
            `project/${project}/category/${category}/label/${label}`,
            params
        );
    },
    destroy(project, category, label, params) {
        return ApiService.delete(
            `project/${project}/category/${category}/label/${label}`,
            { data: params }
        );
    },
    bulkDestroy(project, category, labels) {
        return ApiService.put(
            `project/${project}/category/${category}/label/bulk`,
            {
                action: "delete",
                labels: labels,
            }
        );
    },
    orders(project, category, params) {
        return ApiService.put(
            `project/${project}/category/${category}/label/orders`,
            params
        );
    },
};
