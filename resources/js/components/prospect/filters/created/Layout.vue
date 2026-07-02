<template>
    <div class="hc-flex-column" style="height: 100%">
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label">
                <icon class="fa fa-calendar" />
                <div class="hc-item-main-content" v-text="'Créé le'"></div>
                <input
                    type="date"
                    v-model="createdAt"
                    class="hc-prospect-filter-created-item-date"
                />
                <icon
                    v-if="createdAt"
                    tag="a"
                    class="fa fa-times"
                    @click.prevent.stop="createdAt = null"
                />
                <icon v-else class="fa fa-plus" />
            </item>
            <item tag="label">
                <icon class="fa fa-hand-point-right" />
                <div class="hc-item-main-content" v-text="'Après le'"></div>
                <input
                    type="date"
                    v-model="createdAfter"
                    class="hc-prospect-filter-created-item-date"
                />
                <icon
                    v-if="createdAfter"
                    tag="a"
                    class="fa fa-times"
                    @click.prevent.stop="createdAfter = null"
                />
                <icon v-else class="fa fa-plus" />
            </item>
            <item tag="label">
                <icon class="fa fa-hand-point-left" />
                <div class="hc-item-main-content" v-text="'Avant le'"></div>
                <input
                    type="date"
                    v-model="createdBefore"
                    class="hc-prospect-filter-created-item-date"
                />
                <icon
                    v-if="createdBefore"
                    tag="a"
                    class="fa fa-times"
                    @click.prevent.stop="createdBefore = null"
                />
                <icon v-else class="fa fa-plus" />
            </item>
            <item tag="label">
                <icon class="fa fa-angle-double-right" />
                <div
                    class="hc-item-main-content"
                    v-text="'Depuis (jours)'"
                ></div>
                <input
                    type="number"
                    v-model.lazy="createdDaysAgo"
                    class="hc-prospect-filter-created-item-date"
                />
                <icon
                    v-if="createdDaysAgo"
                    tag="a"
                    class="fa fa-times"
                    @click.prevent.stop="createdDaysAgo = null"
                />
                <icon v-else class="fa fa-plus" />
            </item>
            <item tag="label">
                <icon class="fa fa-angle-double-left" />
                <div
                    class="hc-item-main-content"
                    v-text="'Avant (jours)'"
                ></div>
                <input
                    v-model.lazy="createdBeforeDays"
                    class="hc-prospect-filter-created-item-date"
                />
                <icon
                    v-if="createdBeforeDays"
                    tag="a"
                    class="fa fa-times"
                    @click.prevent.stop="createdBeforeDays = null"
                />
                <icon v-else class="fa fa-plus" />
            </item>
        </item-list>
    </div>
</template>

<style>
.hc-prospect-filter-created-item {
    padding: 3px 0 !important;
    align-items: start;
}

.hc-prospect-filter-created-item-title {
    color: #000000;
}
.hc-prospect-filter-created-item-value {
    color: #000000;
    font-size: 11px;
    opacity: 0.7;
}

.hc-prospect-filter-created-item-date {
    width: 90px;
    border: 1px solid #ccc;
    border-radius: 3px;
    padding: 0 0 0 5px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    REMOVE_PROSPECT_PARAMS,
    ADD_PROSPECT_PARAMS,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";

export default {
    data() {
        return {};
    },

    methods: {},

    computed: {
        ...mapGetters([
            "users",
            "prospect",
            "prospectFullName",
            "prospectsParamValue",
            "prospectsParamExists",
            "can",
        ]),

        ...Object.fromEntries(
            [
                "createdAfter",
                "createdBefore",
                "createdDaysAgo",
                "createdBeforeDays",
            ].map((key) => [
                key,
                {
                    get() {
                        // Check if event params exists
                        if (!this.prospectsParamExists(key)) {
                            return null;
                        }

                        return this.prospectsParamValue(key);
                    },
                    // Set selected field CSS value
                    set(value) {
                        // Remove event params to
                        // prospect params
                        store.commit(REMOVE_PROSPECT_PARAMS, { key });

                        if (value !== null) {
                            // Add event params to
                            // prospect params
                            store.commit(ADD_PROSPECT_PARAMS, { key, value });
                        }

                        store.dispatch(FETCH_PROSPECTS);
                    },
                },
            ])
        ),
    },
};
</script>
