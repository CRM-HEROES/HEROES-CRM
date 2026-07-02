import ApiService from "@/apis/api.service";

export default {
    get(project) {
        return ApiService.get(`project/${project}/google/drive`);
    },
    destroy(project, slug) {
        return ApiService.delete(`project/${project}/google/drive/${slug}`);
    },
};
