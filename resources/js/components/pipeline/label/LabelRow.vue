<template>
    <item tag="label" v-tooltip="label.name">
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
        <checkbox
            :model-value="value"
            @change="change"
        />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import {
    ADD_PIPELINE_LABEL,
    REMOVE_PIPELINE_LABEL,
} from "@/actions/project/pipeline/label";

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
        change(event) {
            store.dispatch(
                event.target.checked
                    ? ADD_PIPELINE_LABEL
                    : REMOVE_PIPELINE_LABEL,
                this.label
            );
            this.$emit("change", event);
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
