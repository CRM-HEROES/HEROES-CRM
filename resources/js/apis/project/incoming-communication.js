import ApiService from "@/apis/api.service";

export default {
    count(project) {
        return ApiService.get(`project/${project}/incoming-communications/count`);
    },
    read(project, prospectId, type, ids) {
        return ApiService.post(`project/${project}/incoming-communications/read`, {
            prospect_id: prospectId, type, ids,
        });
    },
    smsHistory(project, prospectId, type = "sms") {
        return ApiService.get(`project/${project}/incoming-communications/sms-history`, {
            params: { prospect_id: prospectId, type },
        });
    },
};
