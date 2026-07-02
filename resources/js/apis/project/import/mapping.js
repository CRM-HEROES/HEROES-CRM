import ApiService from "@/apis/api.service";

export default {
    get(project, projectImport, params) {
        return ApiService.get(
            `project/${project}/import/${projectImport}/mapping`,
            params
        );
    },
    updateField(project, projectImport, index, field) {
        return ApiService.put(
            `project/${project}/import/${projectImport}/mapping/${index}/field/${field}`
        );
    },
    updateMeta(project, projectImport, index, meta) {
        return ApiService.put(
            `project/${project}/import/${projectImport}/mapping/${index}/meta/${meta}`
        );
    },
    updateCategory(project, projectImport, index, category) {
        return ApiService.put(
            `project/${project}/import/${projectImport}/mapping/${index}/category/${category}`
        );
    },
    updateThread(project, projectImport, index, thread) {
        return ApiService.put(
            `project/${project}/import/${projectImport}/mapping/${index}/thread/${thread}`
        );
    },
    updateEvent(project, projectImport, index) {
        return ApiService.put(
            `project/${project}/import/${projectImport}/mapping/${index}/event`
        );
    },
    updateOrder(project, projectImport, index) {
        return ApiService.put(
            `project/${project}/import/${projectImport}/mapping/${index}/order`
        );
    },
    updateSms(project, projectImport, index) {
        return ApiService.put(
            `project/${project}/import/${projectImport}/mapping/${index}/sms`
        );
    },
    updateInteraction(project, projectImport, index) {
        return ApiService.put(
            `project/${project}/import/${projectImport}/mapping/${index}/interaction`
        );
    },
    updateLink(project, projectImport, index) {
        return ApiService.put(
            `project/${project}/import/${projectImport}/mapping/${index}/link`
        );
    },
    updateUser(project, projectImport, index) {
        return ApiService.put(
            `project/${project}/import/${projectImport}/mapping/${index}/user`
        );
    },
    destroy(project, projectImport, index) {
        return ApiService.delete(
            `project/${project}/import/${projectImport}/mapping/${index}`
        );
    },
};
