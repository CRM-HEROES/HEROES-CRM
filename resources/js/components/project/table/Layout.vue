<template>
    <div id="projects-table">
        <div id="projects-table-body">
            <project-table />
        </div>

        <card-menus />

        <user-connection-stat-slide />
    </div>
</template>

<style>
#projects-table {
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100%;
    border-top: 1px solid #c0c0c0;
}
#projects-table-body {
    display: flex;
    flex-direction: row;
    flex: 1;
    width: 100%;
    overflow: hidden;
}
</style>

<script>
import CardMenus from "./CardMenus.vue";
import ProjectTable from "./Table.vue";
import UserConnectionStatSlide from "@/components/stat/slides/user-connection-stat/Slide.vue";

export default {
    components: {
        CardMenus,
        ProjectTable,
        UserConnectionStatSlide,
    },
};
</script>
