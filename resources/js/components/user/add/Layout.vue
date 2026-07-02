<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <div class="hc-flex-column" style="height: 100%">
                <search v-model="email" :placeholder="$t('user.add.email')" />
                <item v-if="user" tag="a" @click.prevent="addUser">
                    <icon class="fa fa-user" />
                    <div class="hc-item-main-content" v-text="userName"></div>
                    <icon class="fa fa-plus" />
                </item>
                <div v-else-if="notFound" tag="a" class="hc-item-list">
                    <span
                        id="hc-user-add-not-found"
                        v-text="$t('user.add.not_found')"
                    ></span>
                    <buttons>
                        <a
                            v-text="$t('user.add.create')"
                            @click.prevent="createUser"
                        ></a>
                    </buttons>
                </div>
                <loading :loading="addingUser" />
            </div>
        </template>

        <template #2>
            <form
                v-if="tab == 1"
                class="hc-flex-column"
                style="height: 100%"
                @submit.prevent="storeUser"
            >
                <item @click="tab = 0" class="bordered">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('user.add.title')"
                    ></div>
                </item>
                <item-list gap="5px">
                    <v-field
                        :label="$t('first_name')"
                        required
                        v-slot="{ label }"
                        ><input
                            required
                            type="text"
                            :placeholder="label + ' ...'"
                            v-model="userToCreate.name"
                    /></v-field>
                    <v-field :label="$t('last_name')" v-slot="{ label }"
                        ><input
                            type="text"
                            :placeholder="label + ' ...'"
                            v-model="userToCreate.last_name"
                    /></v-field>
                    <v-field :label="$t('email')" required v-slot="{ label }"
                        ><input
                            required
                            type="email"
                            :placeholder="label + ' ...'"
                            v-model="userToCreate.email"
                    /></v-field>
                    <v-field
                        :label="$t('password')"
                        required
                        v-slot="{ label }"
                    >
                        <div class="hc-flex-row">
                            <input
                                required
                                :type="showPassword ? 'text' : 'password'"
                                :placeholder="label + ' ...'"
                                class="hc-flex-1"
                                v-model="userToCreate.password"
                            />
                            <icon
                                @click.prevent="showPassword = !showPassword"
                                tag="a"
                                :class="
                                    showPassword
                                        ? 'fa fa-eye-slash'
                                        : 'fa fa-eye'
                                "
                                :size="34"
                            />
                        </div>
                    </v-field>
                    <item tag="label">
                        <icon class="fa fa-plus" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('user.add.create_professional_project')"
                        ></div>
                        <checkbox
                            value="professional"
                            v-model="userToCreate.default_projects"
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
                            v-model="userToCreate.default_projects"
                        />
                    </item>
                    <item tag="label">
                        <icon class="fa fa-plus" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('user.add.create_demo_project')"
                        ></div>
                        <checkbox
                            value="demo"
                            v-model="userToCreate.default_projects"
                        />
                    </item>
                    <template v-if="showMore">
                        <v-field :label="$t('phone_number')" v-slot="{ label }"
                            ><input
                                type="text"
                                :placeholder="label + ' ...'"
                                v-model="userToCreate.phone_number"
                        /></v-field>
                        <v-field
                            :label="$t('mobile_phone_number')"
                            v-slot="{ label }"
                            ><input
                                type="text"
                                :placeholder="label + ' ...'"
                                v-model="userToCreate.mobile_phone_number"
                        /></v-field>
                        <v-field :label="$t('street')" v-slot="{ label }">
                            <google-map-input
                                v-model="userToCreate.street"
                                @changed="setAddress"
                                :placeholder="label + ' ...'"
                            >
                            </google-map-input>
                        </v-field>
                        <v-field :label="$t('street_bis')" v-slot="{ label }"
                            ><input
                                type="text"
                                :placeholder="label + ' ...'"
                                v-model="userToCreate.street_bis"
                        /></v-field>
                        <v-field :label="$t('postal_code')" v-slot="{ label }"
                            ><input
                                type="text"
                                :placeholder="label + ' ...'"
                                v-model="userToCreate.postal_code"
                        /></v-field>
                        <v-field :label="$t('city')" v-slot="{ label }"
                            ><input
                                type="text"
                                :placeholder="label + ' ...'"
                                v-model="userToCreate.city"
                        /></v-field>
                        <v-field :label="$t('country')" v-slot="{ label }"
                            ><input
                                type="text"
                                :placeholder="label + ' ...'"
                                v-model="userToCreate.country"
                        /></v-field>
                    </template>
                    <item tag="a" @click.prevent="showMore = !showMore">
                        <div
                            class="hc-item-main-content"
                            v-text="
                                showMore ? $t('show_less') : $t('show_more')
                            "
                        ></div>
                        <icon
                            :class="
                                showMore ? 'fa fa-caret-up' : 'fa fa-caret-down'
                            "
                        />
                    </item>
                </item-list>
                <buttons>
                    <button v-text="$t('add')"></button>
                </buttons>
                <loading :loading="creatingUser" />
            </form>
        </template>
    </tab-layout>
</template>

<style>
#hc-user-add-not-found {
    padding: 10px;
    font-size: 13px;
    color: #880000;
    text-align: center;
    white-space: normal;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import UserService from "@/apis/user";

// Actions
import { ADD_USER, SET_USER } from "@/actions/project/user";
import { ADD_GLOBAL_USER } from "@/actions/user";
import { CLOSE_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            tab: 0,
            email: "",
            user: null,
            addingUser: false,
            notFound: false,

            userToCreate: this.newUser(),
            showMore: false,
            creatingUser: false,
            showPassword: false,
        };
    },

    methods: {
        async addUser() {
            this.addingUser = true;
            try {
                const data = await store.dispatch(ADD_USER, {
                    email: this.user.email,
                });

                // this.showUser(data);
            } catch (e) {
                flashError({
                    title: "",
                    body: e.response.data.message,
                });
            } finally {
                this.addingUser = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        newUser() {
            return {
                name: "",
                last_name: "",
                email: this.email,
                password: "",
                street: "",
                street_bis: "",
                postal_code: "",
                city: "",
                country: "",
                default_projects: [],
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
        createUser() {
            this.userToCreate = this.newUser();
            this.tab = 1;
        },

        /**
         *
         */
        async storeUser() {
            this.creatingUser = true;

            try {
                this.user = await store.dispatch(
                    ADD_GLOBAL_USER,
                    this.userToCreate
                );
                this.tab = 0;
                this.addUser();
            } catch (e) {
                flashError({
                    title: "Nouvel utilisateur",
                    body:
                        "Erreur lors de la création de nouvel utilisateur (" +
                        e.response.data.message +
                        ")",
                });
            } finally {
                this.creatingUser = false;
            }
        },

        /**
         * Go to the user profile
         */
        showUser(user) {
            store.commit(SET_USER, user);
            this.$router.push({
                name: "user.show",
                params: {
                    project: this.project.slug,
                    user: user.id,
                },
            });
        },
    },

    watch: {
        async email(newValue) {
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(newValue)) {
                return;
            }

            try {
                const { data } = await UserService.email(newValue);
                this.user = data;
                this.notFound = !data;
            } catch (e) {}
        },
    },

    computed: {
        ...mapGetters(["project"]),

        userName() {
            if (!this.user) {
                return "";
            }

            return [this.user.name, this.user.last_name]
                .filter((u) => u)
                .join(" ");
        },
    },
};
</script>
