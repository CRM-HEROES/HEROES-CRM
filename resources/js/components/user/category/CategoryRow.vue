<template>
    <item tag="label">
        <icon class="fa fa-tags" :style="style" />
        <div class="hc-item-main-content" v-text="category.name"></div>
        <icon
            v-if="can('all.project.category.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <checkbox :model-value="value" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    ADD_USER_CATEGORY,
    REMOVE_USER_CATEGORY,
} from "@/actions/project/user/category";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_CATEGORY } from "@/actions/project/category";

export default {
    props: {
        value: {
            type: Boolean,
            default: false,
        },

        category: {
            type: Object,
        },
    },

    data() {
        return {
            currentValue: this.value,
        };
    },

    methods: {
        change(event) {
            store.dispatch(
                event.target.checked ? ADD_USER_CATEGORY : REMOVE_USER_CATEGORY,
                this.category
            );
            this.$emit("change", event);
        },

        edit() {
            store.commit(OPEN_MODAL, "category-update");
            store.commit(SET_CATEGORY, this.category);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        style() {
            return {
                color: this.category.bgcolor,
            };
        },
    },
};
</script>
