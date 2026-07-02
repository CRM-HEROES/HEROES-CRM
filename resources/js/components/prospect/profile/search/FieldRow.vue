<template>
    <item v-tooltip="field.name">
        <icon class="fa fa-columns" />
        <div
            class="hc-prospect-profile-search-field-label"
            v-text="field.name + ': '"
        ></div>
        <div
            class="hc-item-main-content"
            v-text="
                field.meta ? prospect.meta[field.slug] : prospect[field.slug]
            "
        ></div>
    </item>
</template>

<style>
.hc-prospect-profile-search-field-label {
    font-weight: bold;
}
</style>

<script>
import { mapGetters } from "vuex";

export default {
    props: {
        field: {
            type: Object,
        },
    },

    computed: {
        ...mapGetters(["prospect"]),
    },
};
</script>
