export default {
    namespaced: true,
    state: {
        changing: false,
    },
    getters: {
        changing(state) {
            return state.changing;
        },
    },
    mutations: {
        setChanging(state, value) {
            state.changing = value;
        },
    },
};
