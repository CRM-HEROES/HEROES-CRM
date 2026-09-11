import ApiService from "@/apis/api.service";

export default {
    send(project, prospect, params) {
        return ApiService.post(
            `project/${project}/prospect/${prospect}/email`,
            params
        );
    },
    sendBulk(project, params) {
        return ApiService.post(`project/${project}/prospect/email/bulk`, params);
    },
};