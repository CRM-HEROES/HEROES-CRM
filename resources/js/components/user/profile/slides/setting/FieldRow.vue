<template>
    <item>
        <icon class="fa fa-columns" />
        <div class="hc-item-main-content" v-text="field.name"></div>
        <icon
            v-if="can('all.project.field.update')"
            class="fa fa-cog hc-show-on-hover"
            @click.stop.prevent="edit"
        />
        <icon class="fa fa-plus" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_FIELD } from "@/actions/project/field";

export default {
    props: {
        field: {
            type: Object,
        },
    },

    methods: {
        edit(e) {
            store.commit(OPEN_MODAL, "field-update");
            store.commit(SET_FIELD, this.field);
        },
    },

    computed: {
        ...mapGetters(["can"]),
    },
};
</script>
