<template>
    <item tag="a" @click.prevent="select">
        <icon class="fa fa-user-tie" />
        <div
            class="hc-item-main-content hc-flex-column"
            v-text="role.name"
        ></div>
    </item>
</template>

<script>
export default {
    props: {
        role: {
            type: Object,
        },
    },

    methods: {
        select() {
            this.$emit("select", this.role);
        },
    },
};
</script>
