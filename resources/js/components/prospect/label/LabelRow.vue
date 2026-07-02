<template>
    <item
        tag="label"
        v-tooltip="label.name"
        :for="
            multiSelection
                ? 'hc-prospect-label-remove-' + label.id
                : 'hc-prospect-label-' + label.id
        "
    >
        <icon class="fa fa-tag" :style="style" />
        <div class="hc-item-main-content" v-text="label.name"></div>
        <div
            v-if="label.prospects_count"
            class="hc-item-count"
            v-text="label.prospects_count"
            @click.prevent.stop="search"
        ></div>
        <icon
            v-if="can('all.project.category.label.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent="edit"
        />
        <input
            v-if="multiSelection"
            :id="'hc-prospect-label-remove-' + label.id"
            type="checkbox"
            :checked="modelValue.includes(label.id)"
            @change="changeSelected"
        />
        <checkbox
            v-else
            :model-value="value"
            @change="change"
            :disabled="prospect.processed_at"
            :id="'hc-prospect-label-' + label.id"
        />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import {
    ADD_PROSPECT_LABEL,
    REMOVE_PROSPECT_LABEL,
} from "@/actions/project/prospect/label";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_LABEL } from "@/actions/project/label";

import {
    INIT_PROSPECT_PARAMS,
    SET_PROSPECT_PARAMS,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";

export default {
    props: {
        modelValue: {
            type: Array,
        },

        value: {
            type: Boolean,
            default: false,
        },

        label: {
            type: Object,
        },

        multiSelection: {
            type: Boolean,
            default: false,
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
                event.target.checked
                    ? ADD_PROSPECT_LABEL
                    : REMOVE_PROSPECT_LABEL,
                this.label
            );
            this.$emit("change", event);
        },

        changeSelected(event) {
            let isChecked = event.target.checked;
            let newValue = [...this.modelValue];
            if (isChecked) {
                newValue.push(this.label.id);
            } else {
                newValue.splice(newValue.indexOf(this.label.id), 1);
            }
            this.$emit("update:modelValue", newValue);
        },

        edit() {
            store.commit(OPEN_MODAL, "label-update");
            store.commit(SET_LABEL, this.label);
        },

        /**
         *
         */
        search() {
            if (this.$route.name == "prospect") {
                store.commit(INIT_PROSPECT_PARAMS);

                store.commit(SET_PROSPECT_PARAMS, this.filter);
                store.dispatch(FETCH_PROSPECTS);
            } else {
                this.$router.push({
                    name: "prospect",
                    params: {
                        project: this.project.slug,
                    },
                    query: this.query,
                });
            }
        },
    },

    computed: {
        ...mapGetters(["project", "prospect", "can"]),

        /**
         *
         */
        style() {
            return {
                color: this.label.bgcolor,
            };
        },

        /**
         *
         */
        filterKey() {
            return "withCategory_" + this.label.category_id;
        },

        /**
         *
         */
        filter() {
            return {
                [this.filterKey]: [this.label.id],
            };
        },

        /**
         *
         */
        query() {
            return {
                filters: JSON.stringify(this.filter),
            };
        },
    },
};
</script>
