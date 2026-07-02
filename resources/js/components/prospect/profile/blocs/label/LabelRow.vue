<template>
    <item class="hc-prospect-active-label" tag="label" @click="manageLabels">
        <icon class="fa fa-tag" :style="style" :size="30" />
        <div class="hc-item-main-content hc-flex-column">
            <span
                class="hc-prospect-active-label-category"
                v-text="category ? category.name : ''"
            ></span>
            <span
                class="hc-prospect-active-label-label"
                v-text="originalLabel.name"
            ></span>
        </div>
        <icon
            v-if="can('all.project.category.label.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <icon
            tag="a"
            class="fa fa-trash icon-red"
            @click.prevent.stop="remove"
            :size="30"
        />
    </item>
</template>

<style>
.hc-prospect-active-label {
    padding: 3px 0 !important;
    align-items: start;
}

.hc-prospect-active-label-category {
    color: #000000;
    font-size: 11px;
    opacity: 0.7;
}

.hc-prospect-active-label-label {
    color: #000000;
    white-space: normal;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { REMOVE_PROSPECT_LABEL } from "@/actions/project/prospect/label";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_LABEL } from "@/actions/project/label";
import { SET_PROSPECT_CATEGORY } from "@/actions/project/prospect";
import { OPEN_SLIDE } from "@/actions/slide";

export default {
    props: {
        value: {
            type: Boolean,
            default: false,
        },

        label: {
            type: Object,
        },
    },

    data() {
        return {
            currentValue: this.value,
        };
    },

    methods: {
        remove() {
            store.dispatch(REMOVE_PROSPECT_LABEL, this.label);
        },

        edit() {
            store.commit(OPEN_MODAL, "label-update");
            store.commit(SET_LABEL, this.label);
        },

        /**
         * Manage prospect labels
         * See: @/components/prospect/label/Slide.vue
         */
        manageLabels() {
            store.commit(
                SET_PROSPECT_CATEGORY,
                this.categories.find((c) => c.id == this.label.category_id)
            );
            store.commit(OPEN_SLIDE, "prospect-manage-labels");
        },
    },

    computed: {
        ...mapGetters(["prospect", "categories", "can"]),

        /**
         *
         */
        style() {
            return {
                color: this.originalLabel.bgcolor,
            };
        },

        /**
         *
         */
        category() {
            return this.categories.find((c) => c.id == this.label.category_id);
        },

        /**
         *
         */
        originalLabel() {
            const label =
                this.category && Array.isArray(this.category.labels)
                    ? this.category.labels.find(
                          (label) => label.id == this.label.id
                      )
                    : undefined;

            return label ? label : this.label;
        },
    },
};
</script>
