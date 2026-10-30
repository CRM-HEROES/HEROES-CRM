import ApiService from "@/apis/api.service";

export default {
    get(project) {
        return ApiService.get(`project/${project}/workflow`);
    },
    create(project, params) {
        return ApiService.post(`project/${project}/workflow`, params);
    },
    show(project, workflow) {
        return ApiService.get(`project/${project}/workflow/${workflow}`);
    },
    /**
     * Name, description and the draft graph (the response lists what is still wrong)
     */
    update(project, workflow, params) {
        return ApiService.put(`project/${project}/workflow/${workflow}`, params);
    },
    destroy(project, workflow) {
        return ApiService.delete(`project/${project}/workflow/${workflow}`);
    },
    /**
     * Problems of the draft, without saving
     */
    validate(project, workflow, params) {
        return ApiService.post(
            `project/${project}/workflow/${workflow}/validate`,
            params
        );
    },
    publish(project, workflow, params) {
        return ApiService.post(
            `project/${project}/workflow/${workflow}/publish`,
            params
        );
    },
    activate(project, workflow) {
        return ApiService.post(`project/${project}/workflow/${workflow}/activate`);
    },
    pause(project, workflow) {
        return ApiService.post(`project/${project}/workflow/${workflow}/pause`);
    },
    archive(project, workflow) {
        return ApiService.post(`project/${project}/workflow/${workflow}/archive`);
    },
    // Follow-up of the executions

    /**
     * One page of executions: { workflow, status ("failed,waiting"), page, per_page }
     */
    executions(project, params) {
        return ApiService.get(`project/${project}/workflow-execution`, { params });
    },
    /**
     * An execution with its steps
     */
    execution(project, execution) {
        return ApiService.get(`project/${project}/workflow-execution/${execution}`);
    },
    cancelExecution(project, execution) {
        return ApiService.post(`project/${project}/workflow-execution/${execution}/cancel`);
    },
    /**
     * Figures of the last days
     */
    stats(project, days) {
        return ApiService.get(`project/${project}/workflow-stats`, { params: { days } });
    },
    /**
     * The workflows a prospect went through, with their steps
     */
    prospectExecutions(project, prospect) {
        return ApiService.get(`project/${project}/prospect/${prospect}/workflow-execution`);
    },

    /**
     * What the builder offers (the backend is the source of truth)
     */
    catalog(project) {
        return ApiService.get(`project/${project}/workflow-catalog`);
    },
    /**
     * Operators of the project (email, sms, call): reference and name only
     */
    providers(project, capability) {
        return ApiService.get(`project/${project}/workflow-provider`, {
            params: capability ? { capability } : {},
        });
    },
};
