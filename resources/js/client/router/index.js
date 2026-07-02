import { createWebHistory, createRouter } from "vue-router";
import store from "@/client/store";

const Main = () => import("@/components/layouts/Main.vue");
const Dashboard = () => import("@/components/dashboard/Dashboard.vue");

const routes = [
    {
        path: "/project/:project",
        component: Main,
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
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
