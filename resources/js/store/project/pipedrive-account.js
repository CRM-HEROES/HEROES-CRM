/**
 * import Store Getters
 */
const getters = {
    /**
     * Get list of Pipedrive accounts
     *
     * @param {*} state
     * @returns
     */
    pipedriveAccounts: (state) =>
        state.project && Array.isArray(state.project.pipedrive_accounts)
            ? state.project.pipedrive_accounts
            : [],
};

/**
 * Store
 */
export default {
    getters,
};
