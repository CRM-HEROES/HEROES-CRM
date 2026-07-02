import ApiService from "@/apis/api.service";

export default {
    show(project, setting) {
        return ApiService.get(`project/${project}/setting/${setting}`);
    },
    update(project, setting, params) {
        return ApiService.put(`project/${project}/setting/${setting}`, {
            value: params,
        });
    },
    destroy(project, setting) {
        return ApiService.delete(`project/${project}/setting/${setting}`);
    },
};
