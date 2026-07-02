<template>
    <item tag="label">
        <icon class="fa fa-tag" :style="style" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <div
            v-if="user.pivot && user.pivot.prospects_count"
            class="hc-item-count"
            v-text="user.pivot.prospects_count"
            @click.prevent.stop="search"
        ></div>
        <icon
            tag="a"
            class="fa fa-thumbs-down"
            :style="excludeStyle"
            :title="
                $t('prospect.table.filters.user.without_user', {
                    user: user.name,
                })
            "
            @click.prevent="toggleExclude"
        />
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import {
    FETCH_PROSPECTS,
    SET_PROSPECT_PARAMS,
    ADD_PROSPECT_PARAMS,
    REMOVE_PROSPECT_PARAMS,
    INIT_PROSPECT_PARAMS,
} from "@/actions/project/prospect";

export default {
    props: {
        user: {
            type: Object,
        },
    },

    methods: {
        change(event) {
            // Remove without param
            store.commit(REMOVE_PROSPECT_PARAMS, {
                key: this.withoutKey,
                value: this.value,
                multiple: true,
            });

            // Add or remove with param
            store.commit(
                event.target.checked
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKey,
                    value: this.value,
                    multiple: true,
                }
            );
            store.dispatch(FETCH_PROSPECTS);
            this.$emit("change", event);
        },

        toggleExclude() {
            // Add or remove with param
            store.commit(
                this.isExcluded ? ADD_PROSPECT_PARAMS : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKey,
                    value: this.value,
                    multiple: true,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcluded ? REMOVE_PROSPECT_PARAMS : ADD_PROSPECT_PARAMS,
                {
                    key: this.withoutKey,
                    value: this.value,
                    multiple: true,
                }
            );

            store.dispatch(FETCH_PROSPECTS);
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
        ...mapGetters(["prospectsParamExists"]),

        /**
         *
         */
        withKey() {
            return "withUsers";
        },

        /**
         *
         */
        withoutKey() {
            return "withoutUsers";
        },

        /**
         *
         */
        value() {
            return this.user.id;
        },

        /**
         *
         */
        isExcluded() {
            return this.prospectsParamExists(this.withoutKey, this.value);
        },

        /**
         *
         */
        isChecked() {
            return (
                this.prospectsParamExists(this.withKey, this.value) ||
                this.prospectsParamExists(this.withoutKey, this.value)
            );
        },

        /**
         *
         */
        style() {
            return {
                color: this.user.bgcolor,
            };
        },

        /**
         *
         */
        excludeStyle() {
            return {
                color: this.isExcluded ? "#CC0000" : "#CCCCCC",
            };
        },

        /**
         *
         */
        filter() {
            return {
                [this.withKey]: [this.user.id],
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
