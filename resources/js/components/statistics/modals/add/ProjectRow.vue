<template>
    <item tag="a" class="hc-stat-chart-add-project">
        <icon>
            <img
                :src="
                    project.thumbnail ? project.thumbnail : '/images/logo.png'
                "
                :alt="project.name"
        /></icon>
        <div class="hc-item-main-content" v-text="project.name"></div>
        <icon class="fa fa-caret-right" />
    </item>
</template>

<style>
.hc-stat-chart-add-project {
    padding: 2px 4px !important;
}
</style>

<script>
export default {
    props: {
        project: {
            type: Object,
        },
    },
};
</script>
