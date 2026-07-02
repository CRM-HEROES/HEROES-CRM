<template>
    <item>
        <icon :class="['fa', icon]" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="thread.name"></div>
        <div
            v-if="thread.project"
            class="hc-global-user-profile-item-project"
            v-text="thread.project.name"
        ></div>
    </item>
</template>

<script>
export default {
    props: {
        thread: {
            type: Object,
        },
    },

    computed: {
        /**
         *
         */
        style() {
            return {
                color: this.thread.bgcolor,
            };
        },

        /**
         *
         */
        icon() {
            return this.thread.private ? "fa-envelope" : "fa-envelope-open";
        },
    },
};
</script>
