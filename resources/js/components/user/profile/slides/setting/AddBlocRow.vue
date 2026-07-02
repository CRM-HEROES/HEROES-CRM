<template>
    <item tag="a" @click.prevent="addBloc">
        <icon :class="bloc.icon" />
        <div class="hc-item-main-content" v-text="bloc.name"></div>
        <icon :class="['fa', bloc.group ? 'fa-caret-right' : 'fa-plus']" />
    </item>
</template>

<script>
export default {
    props: {
        bloc: {
            type: Object,
        },
    },

    methods: {
        addBloc() {
            this.$emit("add-bloc", this.bloc);
        },
    },
};
</script>
