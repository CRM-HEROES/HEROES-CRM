<template>
    <item @click="edit">
        <icon class="fa fa-columns" :size="28" :style="style" />
        <div class="hc-item-main-content" v-text="field.name"></div>
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
        edit(e) {
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
