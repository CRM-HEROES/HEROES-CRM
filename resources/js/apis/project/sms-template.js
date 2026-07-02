import ApiService from "@/apis/api.service";

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/sms-template`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/sms-template`, params);
    },
    show(project, smsTemplate) {
        return ApiService.get(`project/${project}/sms-template/${smsTemplate}`);
    },
    update(project, smsTemplate, params) {
        return ApiService.put(
            `project/${project}/sms-template/${smsTemplate}`,
            params
        );
    },
    destroy(project, smsTemplate) {
        return ApiService.delete(
            `project/${project}/sms-template/${smsTemplate}`
        );
    },
};
