<template>
    <slide
        name="user-manage-phone-country-codes"
        title="Ligne affectée"
        icon="fa fa-phone"
        style="width: 260px"
        @open="prepareSelection"
    >
        <div class="hc-phone-code-slide">
            <search v-model="search" />
            <item-list class="hc-flex-1" padding="5px">
                <item
                    v-for="country in filteredCountries"
                    :key="country.country_code"
                    tag="label"
                >
                    <icon class="fa fa-phone" />
                    <div class="hc-item-main-content">
                        {{ country.country_name }} ({{ country.calling_code }})
                    </div>
                    <checkbox
                        :model-value="selectedCountryCodes.includes(country.country_code)"
                        :disabled="loading"
                        @change="changeCountry(country, $event)"
                    />
                </item>
                <item v-if="!filteredCountries.length">Aucun pays trouvé.</item>
            </item-list>
            <p v-if="saveError" class="hc-phone-code-error" role="alert">
                L’enregistrement a échoué. Réessayez.
            </p>
            <loading :loading="loading || saving" />
        </div>
    </slide>
</template>

<style scoped>
.hc-phone-code-slide {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.hc-phone-code-error {
    margin: 5px 10px;
    color: #a32121;
}
</style>

<script>
import { all as getCountryCodes } from "country-codes-list";
import UserSettingService from "@/apis/project/user/setting";
import { SET_ASSIGNED_PHONE_COUNTRY_CODES } from "@/actions/project/user/setting";
import store from "@/store";
import { mapGetters } from "vuex";

const SETTING_KEY = "assigned-phone-country-codes";
const SLIDE_NAME = "user-manage-phone-country-codes";
const countryNames = new Intl.DisplayNames(["fr"], { type: "region" });

export default {
    data() {
        return {
            search: "",
            countries: getCountryCodes()
                .filter((country) => country.countryCallingCode)
                .map((country) => ({
                    country_code: country.countryCode,
                    country_name:
                        countryNames.of(country.countryCode) ||
                        country.countryNameEn,
                    calling_code: `+${country.countryCallingCode}`,
                }))
                .sort((first, second) =>
                    first.country_name.localeCompare(second.country_name, "fr")
                ),
            selectedCountryCodes: [],
            loading: false,
            saving: false,
            saveError: false,
            selectionRevision: 0,
            savedRevision: 0,
        };
    },

    computed: {
        ...mapGetters(["project", "user"]),

        filteredCountries() {
            const query = this.search
                .trim()
                .toLocaleLowerCase("fr")
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "");

            if (!query) {
                return this.countries;
            }

            return this.countries.filter((country) => {
                const searchable = `${country.country_name} ${country.country_code} ${country.calling_code}`
                    .toLocaleLowerCase("fr")
                    .normalize("NFD")
                    .replace(/[\u0300-\u036f]/g, "");

                return searchable.includes(query);
            });
        },
    },

    methods: {
        async prepareSelection() {
            this.search = "";
            this.saveError = false;
            this.loading = true;
            this.selectionRevision = 0;
            this.savedRevision = 0;

            try {
                const { data } = await UserSettingService.show(
                    this.project.slug,
                    this.user.id,
                    SETTING_KEY
                );
                const assignedCountries = Array.isArray(data) ? data : [];
                this.selectedCountryCodes = assignedCountries.map(
                    (country) => country.country_code
                );
                store.commit(SET_ASSIGNED_PHONE_COUNTRY_CODES, {
                    project: this.project.slug,
                    user: this.user.id,
                    countries: assignedCountries,
                });
            } catch (error) {
                this.saveError = true;
            } finally {
                this.loading = false;
            }
        },

        changeCountry(country, event) {
            const selectedCodes = new Set(this.selectedCountryCodes);
            if (event.target.checked) {
                selectedCodes.add(country.country_code);
            } else {
                selectedCodes.delete(country.country_code);
            }
            this.selectedCountryCodes = Array.from(selectedCodes);
            this.selectionRevision += 1;
            this.persistSelection();
        },

        async persistSelection() {
            if (this.saving || this.loading) {
                return;
            }

            this.saving = true;
            try {
                while (this.savedRevision < this.selectionRevision) {
                    const revision = this.selectionRevision;
                    const selectedCountries = this.countries.filter((country) =>
                        this.selectedCountryCodes.includes(country.country_code)
                    );

                    await UserSettingService.update(
                        this.project.slug,
                        this.user.id,
                        SETTING_KEY,
                        selectedCountries
                    );
                    store.commit(SET_ASSIGNED_PHONE_COUNTRY_CODES, {
                        project: this.project.slug,
                        user: this.user.id,
                        countries: selectedCountries,
                    });
                    this.savedRevision = revision;
                }
                this.saveError = false;
            } catch (error) {
                this.saveError = true;
            } finally {
                this.saving = false;
            }
        },
    },
};
</script>