import { createStore } from "vuex";
import createPersistedState from "vuex-persistedstate";

import auth from "@/store/auth";
import event from "@/store/event";
import defaultField from "@/store/default-field";
import locale from "@/store/locale";
import modal from "@/store/modal";
import permission from "@/store/permission";
import project from "@/store/project";
import slide from "@/store/slide";
import statChart from "@/store/stat-chart";
import route from "@/store/route";
import user from "@/store/user";

const store = createStore({
    plugins: [createPersistedState()],
    modules: {
        auth,
        event,
        defaultField,
        locale,
        modal,
        permission,
        project,
        route,
        slide,
        statChart,
        user,
    },
    mutations: {
        RESET(state) {
            state.project.projects = [];
            state.project.project = {};
            state.user.userSessions = [];
            state.user.globalUsers = [];
            state.user.userGeoips = [];
        },
    },
});
export default store;
