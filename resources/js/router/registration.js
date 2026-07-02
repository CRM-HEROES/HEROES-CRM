// User registration
const UserRegistrationLayout = () =>
    import("@/components/auth/registration/Registration.vue");

const UserRegistrationStep = () =>
    import("@/components/auth/registration/Step.vue");
/*
const UserRegistrationStepSettingProspectsTable = () =>
    import(
        "@/components/auth/registration/setting/prospects-table-column/Setting.vue"
    );
*/
export default [
    {
        path: "/registration",
        component: UserRegistrationLayout,
        meta: {
            middleware: "auth",
        },
        children: [
            {
                component: UserRegistrationStep,
                name: "registration.step",
                path: "step/:step",
                meta: {
                    title: `Registration`,
                    middleware: "auth",
                },
            },
            {
                component: UserRegistrationStep,
                name: "registration.step.project",
                path: "step/:step/:project",
                meta: {
                    title: `Registration`,
                    middleware: "auth",
                },
            },
            /*{
                component: UserRegistrationStepProject,
                name: "registration.project",
                path: "project",
                meta: {
                    title: `Registration`,
                    middleware: "auth",
                },
            },
            {
                component: UserRegistrationStepSettingProspectsTable,
                name: "registration.setting.prospects-table",
                path: "setting/prospects-table",
                meta: {
                    title: `Registration`,
                    middleware: "auth",
                },
            },*/
        ],
    },
];
