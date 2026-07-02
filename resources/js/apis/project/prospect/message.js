import ApiService from "@/apis/api.service";

export default {
    get(project, prospect, thread, params) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/thread/${thread}/message`,
            params
        );
    },
    create(project, prospect, thread, params) {
        return ApiService.post(
            `project/${project}/prospect/${prospect}/thread/${thread}/message`,
            params
        );
    },
    bulk(project, thread, params) {
        return ApiService.post(
            `project/${project}/thread/${thread}/message/bulk`,
            params
        );
    },
    show(project, prospect, thread, message) {
        return ApiService.get(
            `project/${project}/prospect/${prospect}/thread/${thread}/message/${message}`
        );
    },
    update(project, prospect, thread, message, params) {
        return ApiService.put(
            `project/${project}/prospect/${prospect}/thread/${thread}/message/${message}`,
            params
        );
    },
    destroy(project, prospect, thread, message) {
        return ApiService.delete(
            `project/${project}/prospect/${prospect}/thread/${thread}/message/${message}`
        );
    },
    addUser(project, prospect, thread, message, user, params) {
        return ApiService.put(
            `project/${project}/prospect/${prospect}/thread/${thread}/message/${message}/user/${user}`,
            params
        );
    },
    removeUser(project, prospect, thread, message, user) {
        return ApiService.delete(
            `project/${project}/prospect/${prospect}/thread/${thread}/message/${message}/user/${user}`
        );
    },
};
