<template>
    <!-- Container -->
    <div style="width: 100%; height: auto">
        <!-- Header -->
        <div
            style="
                width: 100%;
                height: 80px;
                padding-top: 28px;
                position: relative;
                overflow: hidden;
                text-align: center;
                background-size: 100% 100%;
            "
        >
            <!-- Header background -->
            <div
                style="
                    position: absolute;
                    background-color: #f5fbfd;
                    width: 170%;
                    height: 200%;
                    bottom: 0;
                    left: 50%;
                    transform: translateX(-50%);
                    border-radius: 50%;
                "
            ></div>

            <img
                style="position: relative; width: 140px"
                src="/images/logo.png"
            />
        </div>

        <!-- Bg -->
        <div
            style="
                width: 100%;
                height: 100px;
                background-image: url(/images/email/bg.png);
                background-size: cover;
            "
        ></div>

        <!-- body -->
        <tab-layout :count="3" :tab="tab" style="width: 100%; height: auto">
            <template #1>
                <step-info @next-step="projectStep" />
            </template>
            <template #2>
                <step-project />
            </template>
        </tab-layout>
    </div>
</template>

<style></style>

<script>
import StepInfo from "./info/Info.vue";
import StepProject from "./project/Project.vue";

export default {
    components: {
        StepInfo,
        StepProject,
    },

    methods: {
        projectStep() {
            this.$router.push({
                name: "registration.step",
                params: {
                    step: "project",
                },
            });
        },
    },

    computed: {
        tab() {
            switch (this.$route.params.step) {
                case "project":
                    return 1;
                default:
                    return 0;
            }
        },
    },
};
</script>
