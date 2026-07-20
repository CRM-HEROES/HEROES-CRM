import { createWebHistory, createRouter } from "vue-router";
import store from "@/store";

import authRoute from "@/router/auth.js";
import userRegistrationRoute from "@/router/registration.js";

import { SET_PROJECT, SHOW_PROJECT } from "@/actions/project";
import { SHOW_PROSPECT } from "@/actions/project/prospect";
import { SHOW_USER } from "@/actions/project/user";
import { SET_GLOBAL_USER, SHOW_GLOBAL_USER } from "@/actions/user";
import {
    SET_DOCUMENT,
    SHOW_DOCUMENT,
    FETCH_DOCUMENTS,
} from "@/actions/project/document";
import { SET_PIPELINE, SHOW_PIPELINE } from "@/actions/project/pipeline";
import {
    FETCH_CAMPAIGNS,
    FETCH_CAMPAIGN_ACTIONS,
    FETCH_CAMPAIGN_RULES,
    FETCH_CAMPAIGN_OPERATORS,
} from "@/actions/project/campaign";
import { CLOSE_MODALS } from "@/actions/modal";
import { CLOSE_SLIDES } from "@/actions/slide";

/* Authenticated Component */
const Main = () => import("@/components/layouts/Main.vue");
const Dashboard = () => import("@/components/dashboard/Dashboard.vue");
// Prospect
const ProspectTable = () => import("@/components/prospect/table/Layout.vue");
const ProspectProfile = () =>
    import("@/components/prospect/profile/Profile.vue");
// Project
const ProjectTable = () => import("@/components/project/table/Layout.vue");
const ProjectProfile = () => import("@/components/project/profile/Profile.vue");
// User
const UserTable = () => import("@/components/user/table/Layout.vue");
const UserProfile = () => import("@/components/user/profile/Profile.vue");
// Map
const Map = () => import("@/components/map/Map.vue");
// Document
const Document = () => import("@/components/document/Document.vue");
// Campaign
const Campaign = () => import("@/components/campaign/Campaign.vue");
// Agenda
const Agenda = () => import("@/components/event/Agenda.vue");
// Pipeline
const Pipeline = () => import("@/components/pipeline/list/Pipeline.vue");
// Stat
const Stat = () => import("@/components/stat/Stat.vue");
// Stat
const Stat2 = () => import("@/components/stat2/Stat.vue");
// Order
const Order = () => import("@/components/order/Table/Layout.vue");

// Error
// 404
const Error404 = () => import("@/components/404.vue");

// const Table = () => import("@/components/table/Test.vue");

const GlobalUserTable = () =>
    import("@/components/global/user/table/Layout.vue");
const GlobalUserProfile = () =>
    import("@/components/global/user/profile/Profile.vue");
/* Authenticated Component */

const routes = [
    ...authRoute,
    ...userRegistrationRoute,
    /*{
        component: Table,
        name: "table",
        path: "/table",
        meta: {
            title: `Table`,
            middleware: "auth",
        },
    },*/
    {
        component: Main,
        path: "/",
        meta: {
            middleware: "auth",
        },
        children: [
            {
                component: Dashboard,
                name: "dashboard",
                path: "",
                meta: {
                    title: `Dashboard`,
                    middleware: "auth",
                },
            },
            {
                component: ProjectTable,
                name: "project",
                path: "project",
                meta: {
                    title: `Projets`,
                    middleware: "auth",
                },
            },
            {
                component: GlobalUserTable,
                name: "global.user",
                path: "user",
                meta: {
                    title: `Utilisateurs`,
                    middleware: "auth",
                },
            },
            {
                component: GlobalUserProfile,
                name: "global.user.show",
                path: "user/:user",
                meta: {
                    title: `Utilisateur`,
                    middleware: "auth",
                },
            },
        ],
    },
    {
        path: "/project/:project",
        component: Main,
        meta: {
            middleware: "auth",
        },
        children: [
            {
                name: "project.show",
                path: "",
                component: ProjectProfile,
                meta: {
                    title: `Projet`,
                    page: "project.show",
                },
            },
            {
                name: "user",
                path: "user",
                component: UserTable,
                meta: {
                    title: `Utilisateurs`,
                    page: "user.index",
                },
            },
            {
                name: "user.show",
                path: "user/:user",
                component: UserProfile,
                meta: {
                    title: `Utilisateur`,
                    page: "user.show",
                },
            },
            {
                name: "prospect",
                path: "prospect",
                component: ProspectTable,
                meta: {
                    title: `Tableau de prospects`,
                    page: "prospect.index",
                },
            },
            {
                name: "prospect.show",
                path: "prospect/:prospect",
                component: ProspectProfile,
                meta: {
                    title: `Fiche prospect`,
                    page: "prospect.show",
                },
            },
            {
                name: "event",
                path: "event",
                component: Agenda,
                meta: {
                    title: `RDV`,
                    page: "agenda",
                },
            },
            {
                name: "order",
                path: "order",
                component: Order,
                meta: {
                    title: `Devis`,
                },
            },
            {
                name: "planning",
                path: "planning",
                redirect: (to) => {
                    const query = { ...(to.query || {}) };
                    if (!query.filters && store.state.auth?.user?.id) {
                        query.filters = JSON.stringify({
                            withUsers: [store.state.auth.user.id],
                        });
                    }

                    return {
                        name: "event",
                        params: {
                            project: to.params.project,
                        },
                        query,
                    };
                },
            },
            {
                name: "map",
                path: "map",
                component: Map,
                meta: {
                    middleware: "auth",
                    page: "map.index",
                    title: `MAP`,
                },
            },
            {
                name: "document",
                path: "document",
                component: Document,
                meta: {
                    title: `Document`,
                },
            },
            {
                name: "document.show",
                path: "document/:document",
                component: Document,
                meta: {
                    title: `Document`,
                },
            },
            {
                name: "campaign",
                path: "campaign",
                component: Campaign,
                meta: {
                    title: `Campagne`,
                },
            },
            {
                name: "campaign.show",
                path: "campaign/:campaign",
                component: Campaign,
                meta: {
                    title: `Campagne`,
                },
            },
            {
                name: "pipeline",
                path: "pipeline",
                component: Pipeline,
                meta: {
                    title: `Pipeline`,
                },
            },
            {
                name: "pipeline.show",
                path: "pipeline/:pipeline",
                component: Pipeline,
                meta: {
                    title: `Pipeline`,
                },
            },
            {
                name: "stat",
                path: "stat",
                component: Stat2,
                meta: {
                    middleware: "auth",
                    page: "stat.index",
                    title: `Statistique`,
                },
            },
            {
                name: "stat2",
                path: "stat2",
                component: Stat,
                meta: {
                    middleware: "auth",
                    page: "stat2.index",
                    title: `Statistique`,
                },
            },
        ],
    },
    {
        path: "/:pathMatch(.*)*",
        name: "NotFound",
        component: Error404,
        meta: {
            title: `Page not found`,
            middleware: "auth",
        },
    },
];

const updateFavicon = (project) => {
    if (!project.thumbnail) {
        return;
    }

    const existingFavicon = document.querySelector('link[rel="icon"]');
    if (existingFavicon) {
        existingFavicon.parentNode.removeChild(existingFavicon);
    }

    const favicon = project.thumbnail;
    const link = document.createElement("link");
    link.rel = "icon";
    link.href = favicon;
    document.head.appendChild(link);
};

const updateTitle = (project) => {
    document.title = project.name;
};

const fetchProject = async (slug) => {
    try {
        const project = await store.dispatch(SHOW_PROJECT, slug);
        updateFavicon(project);
        updateTitle(project);
    } finally {
    }
};

const fetchUser = async (id) => {
    try {
        store.dispatch(SHOW_GLOBAL_USER, id);
    } finally {
    }
};

const router = createRouter({
    history: createWebHistory(),
    routes, // short for `routes: routes`
});

router.beforeEach(async (to, from, next) => {
    store.commit("route/setChanging", true);

    document.title = to.meta.title;
    if (to.meta.middleware == "auth") {
        if (
            store.state.auth.user &&
            store.state.auth.user.id &&
            store.state.auth.authenticated
        ) {
            store.commit(SET_GLOBAL_USER, null);

            if (
                store.state.auth.impersonating &&
                store.state.auth.impersonate_project &&
                (!to.params.project ||
                    to.params.project != store.state.auth.impersonate_project)
            ) {
                next({
                    name: "prospect",
                    params: {
                        project: store.state.auth.impersonate_project,
                    },
                });
            }

            if (to.params.project) {
                if (
                    !store.state.project ||
                    !store.state.project.project ||
                    store.state.project.project.slug != to.params.project ||
                    from.name === undefined
                ) {
                    fetchProject(to.params.project);
                } else {
                    updateFavicon(store.state.project.project);
                    updateTitle(store.state.project.project);
                }
            }

            if (to.name == "document") {
                store.commit(SET_DOCUMENT, null);
                store.dispatch(FETCH_DOCUMENTS);
            } else if (to.name == "document.show") {
                store.commit(SET_DOCUMENT, null);
                store.dispatch(SHOW_DOCUMENT, to.params.document);
            } else if (to.name == "campaign" || to.name == "campaign.show") {
                store.dispatch(FETCH_CAMPAIGNS);
                store.dispatch(FETCH_CAMPAIGN_ACTIONS);
                store.dispatch(FETCH_CAMPAIGN_RULES);
                store.dispatch(FETCH_CAMPAIGN_OPERATORS);
            } else if (to.name == "prospect.show") {
                try {
                    await store.dispatch(SHOW_PROSPECT, to.params.prospect);
                } catch (e) {
                    next({ name: "NotFound" });
                }
            } else if (to.name == "user.show") {
                store.dispatch(SHOW_USER, to.params.user);
            } else if (to.name == "global.user.show") {
                store.dispatch(SHOW_GLOBAL_USER, to.params.user);
            } else if (to.name == "pipeline") {
                store.commit(SET_PIPELINE, null);
            } else if (to.name == "pipeline.show") {
                store.commit(SET_PIPELINE, null);
                store.dispatch(SHOW_PIPELINE, to.params.pipeline);
            }
            next();
            return;
        } else {
            await axios
                .get("/api/auth")
                .then(({ data }) => {
                    store.commit("auth/SET_USER", data);
                    store.commit("auth/SET_AUTHENTICATED", true);
                    store.commit(
                        "auth/SET_IMPERSONATING",
                        data.is_impersonating
                    );
                    store.commit(
                        "auth/SET_IMPERSONATE_PROJECT",
                        data.impersonating_project
                    );
                    next({ name: "dashboard" });
                })
                .catch((e) => {
                    next({ name: "login" });
                });
            return;
        }
    } else {
        if (to.name == "password.request") {
            store.commit("auth/SET_PASSWORD_RESET_TOKEN", to.params.token);
        }

        if (store.state.auth.authenticated) {
            next({ name: "dashboard" });
            return;
        }
        next();
        return;
    }
});

router.afterEach(async (to, from) => {
    store.commit("route/setChanging", false);
    if (to.name != from.name) {
        store.commit(CLOSE_SLIDES);
        store.commit(CLOSE_MODALS);
    }

    if (!to.params.project) {
        store.commit(SET_PROJECT, null);
    }
});

export default router;
