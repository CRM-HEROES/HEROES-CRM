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
    test(project, setting) {
        return ApiService.post(`project/${project}/setting/${setting}/test`);
    },
    diagnoseCalls(project, prospectId) {
        return ApiService.post(`project/${project}/setting/calls/diagnostic`, {
            prospect_id: prospectId,
        });
    },
    destroy(project, setting) {
        return ApiService.delete(`project/${project}/setting/${setting}`);
    },
};
