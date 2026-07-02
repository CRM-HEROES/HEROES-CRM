export default {
    namespaced: false,
    state: {
        locale: "fr",
    },
    getters: {
        locale(state) {
            return state.locale;
        },
    },
    mutations: {
        setLocale(state, value) {
            state.locale = value;
        },
    },
};
