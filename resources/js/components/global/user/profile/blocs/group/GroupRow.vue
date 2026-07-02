<template>
    <item>
        <icon class="fa fa-users" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="group.name"></div>
        <div
            v-if="group.project"
            class="hc-global-user-profile-item-project"
            v-text="group.project.name"
        ></div>
    </item>
</template>

<script>
export default {
    props: {
        group: {
            type: Object,
        },
    },

    computed: {
        /**
         *
         */
        style() {
            return {
                color: this.group.bgcolor,
            };
        },
    },
};
</script>
