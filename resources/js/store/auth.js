import axios from "axios";
import router from "../router";

export default {
    namespaced: true,
    state: {
        authenticated: false,
        impersonating: false,
        impersonate_project: null,
        passwordResetToken: null,
        user: {},
    },
    getters: {
        authenticated(state) {
            return state.authenticated;
        },
        user(state) {
            return state.user;
        },
        userFullName(state) {
            return [state.user.name, state.user.last_name]
                .filter((e) => e)
                .join(" ");
        },
        impersonating(state) {
            return state.impersonating;
        },
        passwordResetToken(state) {
            return state.passwordResetToken;
        },
    },
    mutations: {
        SET_AUTHENTICATED(state, value) {
            state.authenticated = value;
        },
        SET_USER(state, value) {
            state.user = value;
        },
        SET_IMPERSONATING(state, value) {
            state.impersonating = value;
        },
        SET_IMPERSONATE_PROJECT(state, value) {
            state.impersonate_project = value;
        },
        SET_PASSWORD_RESET_TOKEN(state, value) {
            state.passwordResetToken = value;
        },
    },
    actions: {
        user({ commit }) {
            return axios
                .get("/api/auth")
                .then(({ data }) => {
                    commit("SET_USER", data);
                    commit("SET_AUTHENTICATED", true);
                    commit("SET_IMPERSONATING", data.is_impersonating);
                    commit(
                        "SET_IMPERSONATE_PROJECT",
                        data.impersonating_project
                    );
                })
                .catch((e) => {
                    commit("SET_USER", {});
                    commit("SET_AUTHENTICATED", false);
                });
        },
        async login({ commit }) {
            try {
                const { data } = await axios.get("/api/auth");
                commit("SET_USER", data);
                commit("SET_AUTHENTICATED", true);
                commit("SET_IMPERSONATING", false);
                commit("SET_IMPERSONATE_PROJECT", null);
                router.push({ name: "dashboard" });
            } catch (e) {
                commit("SET_USER", {});
                commit("SET_AUTHENTICATED", false);
            }
        },
        async loginWithoutRedirect({ commit }) {
            try {
                const { data } = await axios.get("/api/auth");
                commit("SET_USER", data);
                commit("SET_AUTHENTICATED", true);
                commit("SET_IMPERSONATING", false);
                commit("SET_IMPERSONATE_PROJECT", null);
            } catch (e) {
                commit("SET_USER", {});
                commit("SET_AUTHENTICATED", false);
            }
        },
        logout({ commit }) {
            commit("SET_USER", {});
            commit("SET_AUTHENTICATED", false);
            router.push({ name: "login" });
        },
    },
};
