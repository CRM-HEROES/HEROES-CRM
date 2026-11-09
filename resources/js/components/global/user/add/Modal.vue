<template>
    <modal
        name="global-user-add"
        :title="$t('user.add.title')"
        @open="user = this.newUser()"
    >
        <form
            class="hc-flex-column"
            style="height: 100%"
            @submit.prevent="storeUser"
        >
            <item-list gap="5px">
                <v-field :label="$t('first_name')" required v-slot="{ label }"
                    ><input
                        required
                        type="text"
                        :placeholder="label + ' ...'"
                        v-model="user.name"
                /></v-field>
                <v-field :label="$t('last_name')" v-slot="{ label }"
                    ><input
                        type="text"
                        :placeholder="label + ' ...'"
                        v-model="user.last_name"
                /></v-field>
                <v-field :label="$t('email')" required v-slot="{ label }"
                    ><input
                        required
                        type="email"
                        :placeholder="label + ' ...'"
                        v-model="user.email"
                /></v-field>
                <item tag="label">
                    <icon class="fa fa-plus" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('user.add.create_professional_project')"
                    ></div>
                    <checkbox
                        value="professional"
                        v-model="user.default_projects"
                    />
                </item>
                <item tag="label">
                    <icon class="fa fa-plus" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('user.add.create_personal_project')"
                    ></div>
                    <checkbox
                        value="personal"
                        v-model="user.default_projects"
                    />
                </item>
                <item tag="label">
                    <icon class="fa fa-plus" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('user.add.create_demo_project')"
                    ></div>
                    <checkbox value="demo" v-model="user.default_projects" />
                </item>
                <template v-if="showMore">
                    <v-field :label="$t('street')" v-slot="{ label }">
                        <google-map-input
                            v-model="user.street"
                            @changed="setAddress"
                            :placeholder="label + ' ...'"
                        >
                        </google-map-input>
                    </v-field>
                    <v-field :label="$t('street_bis')" v-slot="{ label }"
                        ><input
                            type="text"
                            :placeholder="label + ' ...'"
                            v-model="user.street_bis"
                    /></v-field>
                    <v-field :label="$t('postal_code')" v-slot="{ label }"
                        ><input
                            type="text"
                            :placeholder="label + ' ...'"
                            v-model="user.postal_code"
                    /></v-field>
                    <v-field :label="$t('city')" v-slot="{ label }"
                        ><input
                            type="text"
                            :placeholder="label + ' ...'"
                            v-model="user.city"
                    /></v-field>
                    <v-field :label="$t('country')" v-slot="{ label }"
                        ><input
                            type="text"
                            :placeholder="label + ' ...'"
                            v-model="user.country"
                    /></v-field>
                </template>
                <item tag="a" @click.prevent="showMore = !showMore">
                    <div
                        class="hc-item-main-content"
                        v-text="showMore ? $t('show_less') : $t('show_more')"
                    ></div>
                    <icon
                        :class="
                            showMore ? 'fa fa-caret-up' : 'fa fa-caret-down'
                        "
                    />
                </item>
            </item-list>
            <small class="temp-password-note">
                Un mot de passe temporaire sera envoyé par email à l'utilisateur (valable 24 h).
            </small>
            <buttons>
                <button v-text="$t('add')"></button>
            </buttons>
            <loading :loading="addingUser" />
        </form>
    </modal>
</template>

<style scoped>
.temp-password-note {
    color: #9ca3af;
    font-size: 12px;
    line-height: 1.4;
    margin-top: 8px;
}
</style>

<script>
import store from "@/store";

// Actions
import { ADD_GLOBAL_USER } from "@/actions/user";
import { CLOSE_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            user: this.newUser(),
            showMore: false,
            addingUser: false,
        };
    },

    methods: {
        /**
         *
         */
        newUser() {
            return {
                name: "",
                last_name: "",
                email: "",
                street: "",
                street_bis: "",
                postal_code: "",
                city: "",
                country: "",
                default_projects: ["professional", "personal", "demo"],
            };
        },

        setAddress(address) {
            this.user = {
                ...this.user,
                street: address.street,
                street_bis: address.street_bis,
                postal_code: address.postal_code,
                city: address.city,
                country: address.country,
            };
        },

        /**
         *
         */
        async storeUser() {
            this.addingUser = true;
            let created = false;

            try {
                await store.dispatch(ADD_GLOBAL_USER, this.user);
                created = true;
            } catch (e) {
                const message =
                    !e.response &&
                    (navigator.onLine === false || e.code === "ERR_NETWORK")
                        ? "Connexion Internet requise pour créer l’utilisateur et envoyer son mot de passe temporaire."
                        : e.response?.data?.message ||
                          "Erreur lors de la création de l’utilisateur.";
                flashError({
                    title: "Nouvel utilisateur",
                    body: message,
                });
            } finally {
                this.addingUser = false;
                if (created) {
                    store.commit(CLOSE_MODAL);
                }
            }
        },
    },
};
</script>
