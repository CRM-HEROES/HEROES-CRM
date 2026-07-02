<template>
    <item>
        <icon class="fa fa-file-upload" />
        <div class="hc-item-main-content" v-text="prospectImport.name"></div>
        <icon class="fa fa-cog hc-show-on-hover" @click.stop.prevent="edit" />
    </item>
</template>

<script>
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_IMPORT } from "@/actions/project/import";

export default {
    props: {
        prospectImport: {
            type: Object,
        },
    },

    methods: {
        edit(e) {
            store.commit(OPEN_MODAL, "import-update");
            store.commit(SET_IMPORT, this.prospectImport);
        },
    },
};
</script>
