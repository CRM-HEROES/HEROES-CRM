<template>
    <item @click.prevent.stop="edit">
        <icon class="fa fa-columns" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="field.name"></div>
        <icon tag="a" class="fa fa-cog" />
    </item>
</template>

<script>
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
        edit() {
            store.commit(OPEN_MODAL, "field-update");
            store.commit(SET_FIELD, this.field);
        },
    },

    computed: {
        /**
         *
         */
        style() {
            return {
                color: this.field.bgcolor,
            };
        },
    },
};
</script>
