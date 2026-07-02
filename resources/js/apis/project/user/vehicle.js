import ApiService from "@/apis/api.service";

export default {
    get(project, user, params) {
        return ApiService.get(
            `project/${project}/user/${user}/vehicle`,
            params
        );
    },
    store(project, user, params) {
        return ApiService.post(
            `project/${project}/user/${user}/vehicle`,
            params
        );
    },
    show(project, user, vehicle) {
        return ApiService.get(
            `project/${project}/user/${user}/vehicle/${vehicle}`
        );
    },
    update(project, user, vehicle, params) {
        return ApiService.put(
            `project/${project}/user/${user}/vehicle/${vehicle}`,
            params
        );
    },
    destroy(project, user, vehicle) {
        return ApiService.delete(
            `project/${project}/user/${user}/vehicle/${vehicle}`
        );
    },
};
