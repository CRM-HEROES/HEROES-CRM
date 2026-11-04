import AgentService from "@/apis/agent";

/**
 * AI agents of the authenticated user (the list shown in the project profile)
 * and the one being edited in the form.
 */
const state = {
    agents: [],
    loaded: false,
    editing: null,
};

const mutations = {
    SET_AGENTS(state, agents) {
        state.agents = [...agents];
        state.loaded = true;
    },
    // null: a new agent
    SET_AGENT_EDITING(state, agent) {
        state.editing = agent ? { ...agent } : null;
    },
};

const actions = {
    async FETCH_AGENTS({ commit }) {
        const { data } = await AgentService.list();
        commit("SET_AGENTS", data);

        return data;
    },
};

const getters = {
    agents: (state) => state.agents,
    agentsLoaded: (state) => state.loaded,
    agentEditing: (state) => state.editing,
};

export default { state, mutations, actions, getters };
