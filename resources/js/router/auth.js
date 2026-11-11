/* Guest Component */
const Login = () => import("@/components/auth/Login.vue");
const Register = () => import("@/components/auth/Register.vue");
const PasswordReset = () => import("@/components/auth/password/Reset.vue");
const PasswordRequest = () => import("@/components/auth/password/Request.vue");
const TwoFactorsEnable = () =>
    import("@/components/auth/two-factors/Enable.vue");
const TwoFactorsLogin = () => import("@/components/auth/two-factors/Login.vue");
const Invitation = () => import("@/components/auth/Invitation.vue");

export default [
    {
        name: "invitation",
        path: "/invitation/:user",
        component: Invitation,
        meta: {
            title: `Activation du compte`,
        },
    },
    {
        name: "login",
        path: "/login",
        component: Login,
        meta: {
            middleware: "guest",
            title: `Login`,
        },
    },
    {
        name: "register",
        path: "/register",
        component: Register,
        meta: {
            middleware: "guest",
            title: `Register`,
        },
    },
    {
        name: "two-factors.enable",
        path: "/two-factors/enable",
        component: TwoFactorsEnable,
        meta: {
            middleware: "auth",
            title: `Two factors`,
        },
    },
    {
        name: "two-factors.login",
        path: "/two-factors/login",
        component: TwoFactorsLogin,
        meta: {
            title: `Two factors`,
        },
    },
    {
        name: "password.reset",
        path: "/password/reset",
        component: PasswordReset,
        meta: {
            title: `Password reset`,
        },
    },
    {
        name: "password.request",
        path: "/password/reset/:token",
        component: PasswordRequest,
        meta: {
            title: `Password request`,
        },
    },
];
