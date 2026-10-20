import ApiService from "@/apis/api.service";

export function smsPayload(params) {
    if (!params.attachments?.length) {
        return params;
    }

    const data = new FormData();
    data.append("source", params.source);
    data.append("message", params.message || "");
    params.attachments.forEach(file => data.append("attachments[]", file, file.name));
    (params.prospects || []).forEach(id => data.append("prospects[]", id));
    return data;
}

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
            smsPayload(params)
        );
    },
    destroy(project, prospect, sms) {
        return ApiService.delete(
            `project/${project}/prospect/${prospect}/sms/${sms}`
        );
    },
    bulk(project, params) {
        return ApiService.post(`project/${project}/sms/bulk`, smsPayload(params));
    },
};
