import ApiService from "@/apis/api.service";

export default {
    get(project, prospect, params) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/sms`,
            params
        );
    },
    create(project, prospect, params) {
        return ApiService.post(
            `project/${project}/prospect/${prospect}/sms`,
            params
        );
    },
    destroy(project, prospect, sms) {
        return ApiService.delete(
            `project/${project}/prospect/${prospect}/sms/${sms}`
        );
    },
    bulk(project, params) {
        return ApiService.post(`project/${project}/sms/bulk`, params);
    },
};
