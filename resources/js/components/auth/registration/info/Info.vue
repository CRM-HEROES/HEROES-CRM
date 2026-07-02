<template>
    <!-- body -->
    <form
        style="width: 100%; height: auto; position: relative"
        @submit.prevent="updateUser"
    >
        <div
            style="
                width: 100%;
                height: auto;
                text-align: center;
                padding-top: 16px;
            "
        >
            <img style="width: 25px" src="/images/email/hi.png" />
            <br />
            <span
                style="
                    font-size: 18px;
                    font-weight: bold;
                    display: inline-block;
                    margin-top: 16px;
                "
                >Bonjour</span
            >
            <br />
            <span
                style="
                    font-size: 14px;
                    text-align: center;
                    display: inline-block;
                    margin-top: 8px;
                    line-height: 24px;
                    width: 100%;
                    padding: 0 30px;
                    box-sizing: border-box;
                "
                >Veuillez complétez vos informations ci-dessous:</span
            >
            <br />
            <div style="display: flex; flex-direction: column">
                <div class="hc-user-registration-info-field-bloc">
                    <div class="hc-user-registration-info-field-bloc-title">
                        <span>Informations</span>
                        <i class="fa fa-info-circle"></i>
                    </div>
                    <div class="hc-user-registration-info-field-bloc-fields">
                        <div
                            class="hc-user-registration-info-field-bloc-fields-row"
                        >
                            <info-row
                                v-model="currentUser.last_name"
                                :label="'Nom'"
                            />
                            <info-row
                                v-model="currentUser.name"
                                :label="'Prénom'"
                            />
                        </div>
                    </div>
                </div>
                <div class="hc-user-registration-info-field-bloc">
                    <div class="hc-user-registration-info-field-bloc-title">
                        <span>Contact</span>
                        <i class="fa fa-phone"></i>
                    </div>
                    <div class="hc-user-registration-info-field-bloc-fields">
                        <div
                            class="hc-user-registration-info-field-bloc-fields-row"
                        >
                            <info-row
                                v-model="currentUser.phone_number"
                                :label="'Téléphone'"
                            />
                            <info-row
                                v-model="currentUser.mobile_phone_number"
                                :label="'Téléphone mobile'"
                            />
                        </div>
                        <div
                            class="hc-user-registration-info-field-bloc-fields-row"
                        >
                            <info-row :label="'Rue'">
                                <google-map-input
                                    v-model="currentUser.street"
                                    @changed="updateAddress"
                                    :placeholder="'Rue'"
                                />
                            </info-row>
                            <info-row
                                v-model="currentUser.street_bis"
                                :label="'Rue 2'"
                            />
                        </div>
                        <div
                            class="hc-user-registration-info-field-bloc-fields-row"
                        >
                            <info-row
                                v-model="currentUser.postal_code"
                                :label="'Code postal'"
                            />
                            <info-row
                                v-model="currentUser.city"
                                :label="'Ville'"
                            />
                        </div>
                        <div
                            class="hc-user-registration-info-field-bloc-fields-row"
                        >
                            <info-row
                                v-model="currentUser.country"
                                :label="'Pays'"
                            />
                        </div>
                    </div>
                </div>
                <div class="hc-user-registration-info-field-bloc">
                    <div class="hc-user-registration-info-field-bloc-title">
                        <span>Paramètre de connexion</span>
                        <i class="fa fa-sign-in"></i>
                    </div>
                    <div class="hc-user-registration-info-field-bloc-fields">
                        <div
                            class="hc-user-registration-info-field-bloc-fields-row"
                        >
                            <info-row :label="'Email'">
                                <input
                                    type="email"
                                    :value="currentUser.email"
                                    disabled
                                />
                            </info-row>
                        </div>
                        <div
                            class="hc-user-registration-info-field-bloc-fields-row"
                        >
                            <info-row :label="'Mot de passe'">
                                <input
                                    type="password"
                                    v-model="currentUser.password"
                                    @change="checkConfirmPassword"
                                    :placeholder="'Mot de passe'"
                                />
                            </info-row>
                            <info-row :label="'Confirmer mot de passe'">
                                <input
                                    type="password"
                                    v-model="confirmPassword"
                                    @change="checkConfirmPassword"
                                    :placeholder="'Confirmer mot de passe'"
                                />
                            </info-row>
                        </div>
                        <div
                            class="hc-user-registration-info-field-bloc-fields-row"
                        >
                            <span
                                v-if="!confirmPasswordValid"
                                class="hc-user-registration-info-field-bloc-fields-row-error"
                                v-text="
                                    'Veuillez vérifier que les 2 mots de passe saisis sont bien correct'
                                "
                            ></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="width: 100%; height: auto">
            <div
                style="
                    width: 100%;
                    height: auto;
                    box-sizing: border-box;
                    padding: 30px 10px;
                    text-align: center;
                "
            >
                <button
                    style="
                        padding: 8px 16px;
                        background-color: rgb(73, 189, 225);
                        color: white;
                        font-size: 15px;
                        border-radius: 4px;
                        border: none;
                    "
                >
                    Suivant
                </button>
            </div>
        </div>

        <loading :loading="fetchingInfo || updatingUser" />
    </form>
</template>

<style>
.hc-user-registration-info-field-bloc {
    display: flex;
    flex-direction: column;
    text-align: left;
    padding: 15px 30px;
}

.hc-user-registration-info-field-bloc-title {
    display: flex;
    flex-direction: row;
}

.hc-user-registration-info-field-bloc-title > span {
    font-weight: bold;
    font-size: 15px;
    flex: 1;
}

.hc-user-registration-info-field-bloc-title > i {
    color: #49bde1;
    font-size: 15px;
}

.hc-user-registration-info-field-bloc-fields {
    display: flex;
    flex-direction: column;
}

.hc-user-registration-info-field-bloc-fields-row {
    display: flex;
    flex-direction: row;
    gap: 15px;
}

.hc-user-registration-info-field-bloc-fields-row-error {
    color: rgb(180, 0, 0);
    font-size: 12px;
}
</style>

<script>
import { mapActions, mapGetters } from "vuex";
import GlobalUserService from "@/apis/user";
import InfoRow from "./InfoRow";

export default {
    components: {
        InfoRow,
    },

    created() {
        this.fetchInfo();
    },

    data() {
        return {
            currentUser: {
                name: "",
                last_name: "",
                phone_number: "",
                mobile_phone_number: "",
                street: "",
                street_bis: "",
                postal_code: "",
                city: "",
                country: "",
                password: "",
                ...this.user,
            },
            confirmPassword: "",
            confirmPasswordValid: true,
            fetchingInfo: false,
            updatingUser: false,
        };
    },

    methods: {
        ...mapActions({
            signIn: "auth/loginWithoutRedirect",
        }),

        async fetchInfo() {
            this.fetchingInfo = true;

            this.signIn().then(async () => {
                try {
                    const { data } = await GlobalUserService.show(this.user.id);
                    this.currentUser = data;
                } finally {
                    this.fetchingInfo = false;
                }
            });
        },

        checkConfirmPassword() {
            this.confirmPasswordValid =
                this.currentUser.password == this.confirmPassword;
        },

        /**
         * Update prospect address
         * @param {*} e
         */
        updateAddress(e) {
            this.currentUser = {
                ...this.currentUser,
                // address fields
                street: e.street,
                postal_code: e.postal_code,
                city: e.city,
                country: e.country,
                state: e.state,
                county: e.county,

                // Lat and Lng
                latitude: e.latitude,
                longitude: e.longitude,
            };
        },

        async updateUser() {
            this.updatingUser = true;

            try {
                await GlobalUserService.update(this.user.id, this.currentUser);
                this.$emit("next-step");
            } finally {
                this.updatingUser = false;
            }
        },
    },

    computed: {
        ...mapGetters("auth", ["user"]),
    },
};
</script>
