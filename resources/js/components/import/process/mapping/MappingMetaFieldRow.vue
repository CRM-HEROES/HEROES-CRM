<template>
    <item>
        <icon class="fa fa-plus-square" />
        <div class="hc-item-main-content" v-text="meta.name"></div>
        <icon
            v-if="can('all.project.field.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
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
        meta: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "field-update");
            store.commit(SET_FIELD, this.meta);
        },
    },

    computed: {
        ...mapGetters(["can"]),
    },
};
</script>
