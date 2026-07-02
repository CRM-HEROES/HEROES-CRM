<template>
    <slide
        name="trash"
        :title="'Corbeille'"
        icon="fa fa-trash"
        style="width: 360px"
    >
        <layout />
    </slide>
</template>

<script>
import Layout from "./Layout.vue";

export default {
    components: {
        Layout,
    },
};
</script>
