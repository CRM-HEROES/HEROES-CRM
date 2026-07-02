<template>
    <item @click.prevent.stop="labels">
        <icon class="fa fa-tags" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="category.name"></div>
        <div
            v-if="category.project"
            class="hc-global-user-profile-item-project"
            v-text="category.project.name"
        ></div>
    </item>
</template>

<script>
export default {
    props: {
        category: {
            type: Object,
        },
    },

    computed: {
        /**
         *
         */
        style() {
            return {
                color: this.category.bgcolor,
            };
        },
    },
};
</script>
