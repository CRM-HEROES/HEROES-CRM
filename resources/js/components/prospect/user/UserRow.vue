<template>
    <item tag="label">
        <icon class="fa fa-user" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <div
            v-if="user.pivot && user.pivot.prospects_count"
            class="hc-item-count"
            v-text="user.pivot.prospects_count"
            @click.prevent.stop="search"
        ></div>
        <icon
            v-if="can('all')"
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
    ADD_PROSPECT_USER,
    REMOVE_PROSPECT_USER,
} from "@/actions/project/prospect/user";
import { SET_USER } from "@/actions/project/user";

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

        user: {
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
                event.target.checked ? ADD_PROSPECT_USER : REMOVE_PROSPECT_USER,
                this.user
            );
            this.$emit("change", event);
        },

        edit() {
            store.commit(SET_USER, this.user);
            this.$router.push({
                name: "user.show",
                params: {
                    project: this.project.slug,
                    user: this.user.id,
                },
            });
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
        ...mapGetters(["project", "can"]),

        /**
         *
         */
        filterKey() {
            return "withUsers";
        },

        /**
         *
         */
        filter() {
            return {
                [this.filterKey]: [this.user.id],
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
