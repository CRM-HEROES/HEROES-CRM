<template>
    <item @click="search">
        <icon class="fa fa-tag" :size="28" :style="style" />
        <div
            class="hc-item-main-content"
            v-text="label.name + ' (' + label.category.name + ')'"
        ></div>
        <icon
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            :size="28"
            @click.prevent.stop="edit"
        />
        <icon
            tag="a"
            class="fa fa-globe-europe"
            color="#007afd"
            :size="28"
            @click.prevent.stop="map"
        />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

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
        label: {
            type: Object,
        },
    },

    methods: {
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

        /**
         *
         */
        map() {
            if (this.$route.name == "map") {
                store.commit(INIT_PROSPECT_PARAMS);

                store.commit(SET_PROSPECT_PARAMS, this.filter);
                store.dispatch(FETCH_PROSPECTS);
            } else {
                this.$router.push({
                    name: "map",
                    params: {
                        project: this.project.slug,
                    },
                    query: this.query,
                });
            }
        },

        edit(e) {
            store.commit(OPEN_MODAL, "label-update");
            store.commit(SET_LABEL, this.label);
        },
    },

    computed: {
        ...mapGetters(["project"]),

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
