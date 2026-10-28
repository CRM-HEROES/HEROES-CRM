import ApiService from "@/apis/api.service";

function templateFormData(params, updating = false) {
    const data = new FormData();
    for (const key of ["name", "body", "subject", "order"]) {
        if (params[key] !== undefined) {
            data.append(key, params[key] ?? "");
        }
    }
    if (Array.isArray(params.attachments)) {
        const retained = params.attachments.filter(attachment => !(attachment instanceof File));
        if (updating && retained.length === 0) {
            data.append("retained_attachment_ids", "");
        }
        retained.forEach(attachment => data.append("retained_attachment_ids[]", attachment.id));
        params.attachments.filter(attachment => attachment instanceof File)
            .forEach(file => data.append("attachments[]", file, file.name));
    }
    if (updating) {
        data.append("_method", "PUT");
    }
    return data;
}

export default {
    get(project, params) {
        return ApiService.get(`project/${project}/message-template`, params);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/message-template`, templateFormData(params));
    },
    show(project, messageTemplate) {
        return ApiService.get(
            `project/${project}/message-template/${messageTemplate}`
        );
    },
    update(project, messageTemplate, params) {
        return ApiService.post(
            `project/${project}/message-template/${messageTemplate}`,
            templateFormData(params, true)
        );
    },
    destroy(project, messageTemplate) {
        return ApiService.delete(
            `project/${project}/message-template/${messageTemplate}`
        );
    },
};
