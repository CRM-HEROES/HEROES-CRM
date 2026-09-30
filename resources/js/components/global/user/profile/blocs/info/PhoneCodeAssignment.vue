<template>
    <bloc icon="fa fa-phone icon-green" name="Ligne affectée" :open="open">
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus icon-blue"
                @click.prevent.stop="openSlide"
            />
        </template>
        <template #body>
            <div class="hc-phone-code-assigned">
                <item
                    v-for="country in assignedCountries"
                    :key="country.country_code"
                >
                    <icon class="fa fa-phone" />
                    <div class="hc-item-main-content">
                        {{ country.country_name }}
                    </div>
                    <span class="hc-phone-code-value">{{ country.calling_code }}</span>
                </item>
                <item v-if="!assignedCountries.length && !loading">
                    Aucune ligne affectée.
                </item>
                <p v-if="saveError" class="hc-phone-code-error" role="alert">
                    L’enregistrement a échoué. Réessayez.
                </p>
                <loading :loading="loading" />
            </div>
        </template>
    </bloc>
</template>

<style scoped>
.hc-phone-code-assignment {
    width: 100%;
}

.hc-phone-code-assigned {
    padding: 5px 10px;
}

.hc-phone-code-value {
    color: #59665f;
    font-variant-numeric: tabular-nums;
}

.hc-phone-code-error {
    margin: 5px 10px;
    color: #a32121;
}
</style>

<script>
import { mapGetters } from "vuex";
import { all as getCountryCodes } from "country-codes-list";
import UserSettingService from "@/apis/project/user/setting";
import ProfileBloc from "@/components/user/profile/blocs/Bloc.vue";
import store from "@/store";
import { OPEN_SLIDE } from "@/actions/slide";
import { SET_ASSIGNED_PHONE_COUNTRY_CODES } from "@/actions/project/user/setting";

const SLIDE_NAME = "user-manage-phone-country-codes";

export default {
    components: {
        Bloc: ProfileBloc,
    },

    props: {
        project: {
            type: Object,
            required: true,
        },
        user: {
            type: Object,
            required: true,
        },
        open: {
            type: Boolean,
            default: deviceType() == "desktop",
        },
    },

    data() {
        return {
            loading: true,
            saveError: false,
        };
    },

    computed: {
        ...mapGetters(["assignedPhoneCountryCodes", "slideOpen"]),

        assignedCountries() {
            return this.assignedPhoneCountryCodes(
                this.project.slug,
                this.user.id
            );
        },

        slideIsOpen() {
            return this.slideOpen(SLIDE_NAME);
        },
    },

    watch: {
        slideIsOpen(isOpen, wasOpen) {
            if (wasOpen && !isOpen) {
                this.fetchAssignedCountries();
            }
        },

        "user.id"() {
            this.fetchAssignedCountries();
        },
    },

    async mounted() {
        this.fetchAssignedCountries();
    },

    methods: {
        async fetchAssignedCountries() {
            this.loading = true;
            this.saveError = false;

            try {
                const { data } = await UserSettingService.show(
                    this.project.slug,
                    this.user.id,
                    "assigned-phone-country-codes"
                );
                store.commit(SET_ASSIGNED_PHONE_COUNTRY_CODES, {
                    project: this.project.slug,
                    user: this.user.id,
                    countries: Array.isArray(data) ? data : [],
                });
            } catch (error) {
                this.saveError = true;
            } finally {
                this.loading = false;
            }
        },

        openSlide() {
            store.commit(OPEN_SLIDE, SLIDE_NAME);
        },
    },
};
</script>
