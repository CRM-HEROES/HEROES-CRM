import axios from "axios";
import { API_URL } from "@/apis/common";
import router from "@/router";
import store from "@/store";

export default {
    init() {
        // axios.defaults.baseURL = API_URL;

        axios.interceptors.response.use(
            (response) => response,
            async (error) => {
                if (error.response && error.response.status === 401) {
                    await store.commit("auth/SET_AUTHENTICATED", false);
                    router.push({ name: "login" });
                } else if (error.response) {
                    if (
                        error.response.status === 422 &&
                        error.response.data &&
                        error.response.data.message == "Two factors validation"
                    ) {
                        await store.commit("auth/SET_AUTHENTICATED", false);
                        router.push({ name: "two-factors.login" });
                    } else {
                        /*flashError({
                            title: "Heroes CRM",
                            body: error.response.data.message
                                ? error.response.data.message
                                : "Request error",
                        });*/
                    }
                }

                return Promise.reject(error);
            }
        );
    },

    get(resource, params) {
        if (params === undefined) {
            params = [];
        }

        return axios.get(`${API_URL}/${resource}`, params);
    },

    post(resource, params, settings) {
        if (params === undefined) {
            params = [];
        }

        if (settings === undefined) {
            settings = [];
        }

        return axios.post(`${API_URL}/${resource}`, params, settings);
    },

    put(resource, params) {
        if (params === undefined) {
            params = [];
        }

        return axios.put(`${API_URL}/${resource}`, params);
    },

    delete(resource, params) {
        if (params === undefined) {
            params = [];
        }

        return axios.delete(`${API_URL}/${resource}`, params);
    },
};
