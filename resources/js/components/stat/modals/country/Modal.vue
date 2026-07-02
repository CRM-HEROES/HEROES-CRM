<template>
    <modal
        :title="$t('stat.country.title', { country: statMapCountry })"
        name="stat-map-country"
        :width="240"
    >
        <div class="hc-flex-column">
            <search v-model="countryKeyword" />
            <item-list class="hc-flex-1" padding="5px">
                <country-row
                    v-for="c in filteredCountries"
                    :key="c"
                    :country="c"
                    @click="mapCountry(c)"
                />
            </item-list>
        </div>
    </modal>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { UPDATE_SETTING } from "@/actions/project/setting";
import { CLOSE_MODAL } from "@/actions/modal";

import CountryRow from "./CountryRow.vue";

export default {
    components: {
        CountryRow,
    },

    data() {
        return {
            countryKeyword: "",
        };
    },

    methods: {
        /**
         *
         * @param {*} country
         */
        mapCountry(country) {
            this.settingsStatMap[this.statMapCountry] = country;
            store.dispatch(UPDATE_SETTING, {
                key: "stat-map",
                value: this.settingsStatMap,
            });
            store.commit(CLOSE_MODAL);
        },
    },

    computed: {
        ...mapGetters([
            "statMapCountries",
            "statMapCountry",
            "settingsStatMap",
        ]),

        /**
         *
         */
        filteredCountries() {
            const keyword = removeStringAccent(this.countryKeyword);

            return this.statMapCountries
                .filter(
                    (country) =>
                        removeStringAccent(country).indexOf(keyword) >= 0
                )
                .sort((c1, c2) => (c1 > c2 ? 1 : -1));
        },
    },
};
</script>
