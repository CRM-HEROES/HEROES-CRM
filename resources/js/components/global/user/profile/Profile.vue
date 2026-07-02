<template>
    <div id="hc-user-profile" v-if="globalUser">
        <div id="hc-user-profile-header">
            <div id="hc-user-profile-title" v-text="globalUser.name"></div>
            <icon
                tag="a"
                class="fa fa-wifi"
                color="#0593ff"
                title="Connexions"
                :icon-size="16"
                @click.prevent="showConnections"
            />
            <icon
                tag="a"
                :class="[
                    'fa',
                    'fa-ban',
                    globalUser.banned_at ? 'icon-grey' : 'icon-red',
                ]"
                :title="
                    globalUser.banned_at
                        ? 'De-bannir cet utilisateur'
                        : 'Bannir définitivement'
                "
                :icon-size="16"
                @click.prevent="toggleBan"
            >
                <loading :loading="banning" />
            </icon>
            <icon
                v-if="!globalUser.banned_at"
                tag="a"
                class="fa fa-sign-in"
                color="#489f1f"
                title="Utiliser"
                :icon-size="16"
                @click.prevent="impersonate"
            >
                <loading :loading="impersonating" />
            </icon>
        </div>
        <div id="hc-user-profile-body">
            <div id="hc-user-profile-body-columns">
                <div v-for="column in userSettingsUserProfile">
                    <template v-for="bloc in column">
                        <info-bloc v-if="bloc.type == 'field'" :bloc="bloc" />
                        <calendar-bloc
                            v-else-if="bloc.type == 'calendar'"
                            :bloc="bloc"
                        />
                        <category-bloc
                            v-else-if="bloc.type == 'category'"
                            :bloc="bloc"
                        />
                        <folder-bloc
                            v-else-if="bloc.type == 'folder'"
                            :bloc="bloc"
                        />
                        <geoip-bloc
                            v-else-if="bloc.type == 'geoip'"
                            :bloc="bloc"
                        />
                        <group-bloc
                            v-else-if="bloc.type == 'group'"
                            :bloc="bloc"
                        />
                        <project-bloc
                            v-else-if="bloc.type == 'project'"
                            :bloc="bloc"
                        />
                        <role-bloc
                            v-else-if="bloc.type == 'role'"
                            :bloc="bloc"
                        />
                        <session-bloc
                            v-else-if="bloc.type == 'session'"
                            :bloc="bloc"
                        />
                        <thread-bloc
                            v-else-if="bloc.type == 'thread'"
                            :bloc="bloc"
                        />
                        <user-bloc
                            v-else-if="bloc.type == 'user'"
                            :bloc="bloc"
                        />
                    </template>
                </div>
                <!--div>
                    <project-bloc />
                </!--div>
                <div>
                    <info-bloc
                        :bloc="{
                            name: 'Profil',
                            items: ['name', 'last_name', 'email', 'password'],
                        }"
                    />
                </div>
                <div>
                    <info-bloc
                        :bloc="{
                            name: 'Coordonnées',
                            items: [
                                'street',
                                'street_bis',
                                'postal_code',
                                'city',
                                'country',
                                'phone_number',
                                'mobile_phone_number',
                            ],
                        }"
                    />
                    <role-bloc
                        :bloc="{
                            name: 'Rôle',
                        }"
                    />
                </div>-->
            </div>
        </div>

        <manage-user-tracker-stat-slide />
        <user-project-slide />
    </div>
</template>

<style scoped>
#hc-user-profile {
    width: 100%;
    display: flex;
    flex-direction: column;
    height: 100%;
    background-color: #f4f4f4;
}

#hc-user-profile-header {
    display: flex;
    flex-direction: row;
    align-items: center;
}

#hc-user-profile-title {
    flex: 1;
    padding: 15px 0 15px 15px;
    font-weight: bold;
    font-size: 15px;
}

#hc-user-profile-body {
    flex: 1;
    overflow: auto;
    display: flex;
    flex-direction: row;
    justify-content: center;
    gap: 15px;
    padding: 0 10px;
}

#hc-user-profile-body-columns {
    width: 1200px;
    max-width: 100%;
    display: flex;
    flex-direction: row;
    padding: 0 0;
    flex-wrap: wrap;
}

#hc-user-profile-body-columns > div {
    flex: 1;
    display: flex;
    flex-direction: column;
    width: 33.333%;
    padding: 7px;
    gap: 15px;
}

#hc-user-profile-logo {
    display: inline-block;
    width: 100%;
    border-radius: 10px;
    cursor: pointer;
}

#hc-user-profile-logo > img {
    width: 100%;
}

@media (max-width: 1000px) {
    #hc-user-profile-body-columns > div {
        min-width: 50%;
        max-width: 50%;
    }
}

@media (max-width: 767px) {
    #hc-user-profile-body-columns {
        flex-direction: row;
    }

    #hc-user-profile-body-columns > div {
        min-width: 100%;
        max-width: 100%;
        flex: none;
    }
}
</style>

<script>
import { mapActions, mapGetters } from "vuex";
import store from "@/store";
import BanService from "@/apis/user/ban";

// Actions
import { UPDATE_GLOBAL_USER } from "@/actions/user";
import { OPEN_SLIDE } from "@/actions/slide";

import CalendarBloc from "./blocs/calendar/Bloc.vue";
import CategoryBloc from "./blocs/category/Bloc.vue";
import FolderBloc from "./blocs/folder/Bloc.vue";
import GeoipBloc from "./blocs/geoip/Bloc.vue";
import GroupBloc from "./blocs/group/Bloc.vue";
import SessionBloc from "./blocs/session/Bloc.vue";
import ThreadBloc from "./blocs/thread/Bloc.vue";
import UserBloc from "./blocs/user/Bloc.vue";

import InfoBloc from "./blocs/info/Bloc.vue";
import RoleBloc from "./blocs/role/Bloc.vue";
import ProjectBloc from "./blocs/project/Bloc.vue";

import ManageUserTrackerStatSlide from "@/components/user/tracker/stat/Slide.vue";
import UserProjectSlide from "../project/Slide";

export default {
    components: {
        CalendarBloc,
        CategoryBloc,
        FolderBloc,
        GeoipBloc,
        GroupBloc,
        InfoBloc,
        SessionBloc,
        ThreadBloc,
        UserBloc,

        InfoBloc,
        RoleBloc,
        ProjectBloc,

        ManageUserTrackerStatSlide,
        UserProjectSlide,
    },

    data() {
        return {
            impersonating: false,
            banning: false,
        };
    },

    methods: {
        ...mapActions({
            signIn: "auth/login",
        }),

        /**
         *
         */
        showConnections() {
            store.commit(OPEN_SLIDE, "user-logs-chart");
        },

        /**
         *
         */
        async toggleBan() {
            if (this.globalUser.banned_at) {
                this.banning = true;

                try {
                    await BanService.cancelBan(this.globalUser.id);

                    store.commit(UPDATE_GLOBAL_USER, {
                        id: this.globalUser.id,
                        banned_at: null,
                    });
                } finally {
                    this.banning = false;
                }
            } else {
                hcConfirm(
                    "Etes-vous sûr de bannir définitivement cet utilisateur ?",
                    async () => {
                        this.banning = true;

                        try {
                            await BanService.ban(this.globalUser.id);

                            store.commit(UPDATE_GLOBAL_USER, {
                                id: this.globalUser.id,
                                banned_at: new Date(),
                            });
                        } finally {
                            this.banning = false;
                        }
                    }
                );
            }
        },

        /**
         *
         */
        async impersonate() {
            this.impersonating = true;
            try {
                await axios.post(
                    "/api/user/" + this.globalUser.id + "/impersonate"
                );
                await this.signIn();
                store.commit("auth/SET_IMPERSONATING", true);

                this.$router.push({
                    name: "dashboard",
                });
            } finally {
                this.impersonating = false;
            }
        },
    },

    computed: {
        ...mapGetters(["globalUser"]),

        userSettingsUserProfile() {
            return [
                [
                    {
                        type: "field",
                        name: "Profil",
                        items: ["name", "last_name", "email", "password"],
                    },
                    {
                        type: "field",
                        name: "Coordonnées",
                        items: [
                            "street",
                            "street_bis",
                            "postal_code",
                            "city",
                            "country",
                            "phone_number",
                            "mobile_phone_number",
                        ],
                    },
                    { type: "project" },
                ],
                [
                    { type: "user" },
                    { type: "role" },
                    { type: "group" },
                    { type: "document" },
                    {
                        type: "category",
                    },
                    { type: "folder" },
                    { type: "thread" },
                    { type: "calendar" },
                    { type: "questionnaire" },
                    { type: "order-step" },
                ],
                [{ type: "geoip" }],
                [{ type: "session" }],
            ];
        },
    },
};
</script>
