<template>
    <slide
        name="locales"
        :title="$t('locale.setting.title')"
        icon="fa fa-earth-americas"
        style="width: 300px"
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
