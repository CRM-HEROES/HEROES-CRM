import ApiService from "@/apis/api.service";

export default {
    get(project, prospect) {
        return ApiService.get(`project/${project}/prospect/${prospect}/folder`);
    },
};
