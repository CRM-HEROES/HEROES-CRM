<template>
    <div class="hc-flex-column" style="height: 100%">
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label" class="hc-prospects-table-filter-interaction">
                <icon class="fa fa-filter" />
                <div
                    class="hc-item-main-content"
                    v-text="
                        $t(
                            'prospect.table.filters.interaction.with_interactions'
                        )
                    "
                ></div>
                <icon
                    tag="a"
                    class="fa fa-thumbs-down"
                    :style="excludeInteractionStyle"
                    :title="
                        $t(
                            'prospect.table.filters.interaction.without_interaction'
                        )
                    "
                    @click.prevent="toggleExcludeInteraction"
                />
                <checkbox
                    :model-value="isCheckedInteraction"
                    @change="changeInteraction"
                />
            </item>
            <source-row
                v-for="source in sources"
                :key="source.key"
                :source="source"
            />
        </item-list>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    REMOVE_PROSPECT_PARAMS,
    ADD_PROSPECT_PARAMS,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";

// Components
import SourceRow from "./SourceRow.vue";

export default {
    components: {
        SourceRow,
    },

    data() {
        return {
            tab: 0,
            sources: [
                {
                    key: "telephone",
                    name: "Téléphone",
                },
                {
                    key: "ringover",
                    name: "Ringover",
                },
                {
                    key: "aircall",
                    name: "Aircall",
                },
            ],
        };
    },

    methods: {
        changeInteraction(event) {
            // Remove without param
            store.commit(REMOVE_PROSPECT_PARAMS, {
                key: this.withoutKeyInteraction,
            });

            // Add or remove with param
            store.commit(
                event.target.checked
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKeyInteraction,
                    value: [],
                }
            );
            store.dispatch(FETCH_PROSPECTS);
        },

        toggleExcludeInteraction() {
            // Add or remove with param
            store.commit(
                this.isExcludedInteraction
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKeyInteraction,
                    value: [],
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedInteraction
                    ? REMOVE_PROSPECT_PARAMS
                    : ADD_PROSPECT_PARAMS,
                {
                    key: this.withoutKeyInteraction,
                    value: [],
                }
            );

            store.dispatch(FETCH_PROSPECTS);
        },
    },

    computed: {
        ...mapGetters([
            "Interactions",
            "prospect",
            "prospectFullName",
            "prospectsParamExists",
            "prospectsParamValue",
        ]),

        /**
         *
         */
        withKeyInteraction() {
            return "withInteractions";
        },

        /**
         *
         */
        withoutKeyInteraction() {
            return "withoutInteractions";
        },

        /**
         *
         */
        isCheckedInteraction() {
            return (
                (this.prospectsParamExists(this.withKeyInteraction) &&
                    this.prospectsParamValue(this.withKeyInteraction).length ==
                        0) ||
                (this.prospectsParamExists(this.withoutKeyInteraction) &&
                    this.prospectsParamValue(this.withoutKeyInteraction)
                        .length == 0)
            );
        },

        /**
         *
         */
        isExcludedInteraction() {
            return (
                this.prospectsParamExists(this.withoutKeyInteraction) &&
                this.prospectsParamValue(this.withoutKeyInteraction).length == 0
            );
        },

        /**
         *
         */
        excludeInteractionStyle() {
            return {
                color: this.isExcludedInteraction ? "#CC0000" : "#CCCCCC",
            };
        },
    },
};
</script>
