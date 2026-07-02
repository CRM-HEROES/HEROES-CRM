<template>
    <item>
        <icon :class="['fa', icon]" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="folder.name"></div>
        <div
            v-if="folder.project"
            class="hc-global-user-profile-item-project"
            v-text="folder.project.name"
        ></div>
    </item>
</template>

<script>
export default {
    props: {
        folder: {
            type: Object,
        },
    },

    computed: {
        /**
         *
         */
        style() {
            return {
                color: this.folder.bgcolor,
            };
        },

        /**
         *
         */
        icon() {
            return this.folder.private ? "fa-folder" : "fa-folder-open";
        },
    },
};
</script>
