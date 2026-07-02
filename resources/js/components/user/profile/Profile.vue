<template>
    <div id="hc-user-profile" v-if="user">
        <div id="hc-user-profile-header">
            <div id="hc-user-profile-title" v-text="user.name"></div>
            <icon
                tag="a"
                class="fa fa-folder icon-blue"
                v-tooltip="$t('user.profile.menus.files')"
                :icon-size="16"
                @click.prevent.stop="manageFiles"
            />
            <icon
                v-if="can('all')"
                tag="a"
                class="fa fa-copy icon-orange"
                v-tooltip="$t('user.profile.menus.duplicate')"
                :icon-size="16"
                @click.prevent="duplicate"
            />
            <icon
                v-if="can('all')"
                tag="a"
                class="fa fa-wifi icon-cyan"
                v-tooltip="$t('user.profile.menus.history')"
                :icon-size="16"
                @click.prevent="showConnections"
            />
            <icon
                v-if="can('all')"
                tag="a"
                class="fa fa-align-left icon-green"
                color="#489f1f"
                v-tooltip="$t('user.profile.menus.actions')"
                :icon-size="16"
                @click.prevent="manageActions"
            />
            <icon
                v-if="can('all')"
                tag="a"
                class="fa fa-hand-paper icon-red"
                v-tooltip="$t('user.profile.menus.permissions')"
                :icon-size="16"
                @click.prevent="managePermissions"
            />
            <icon
                tag="a"
                class="fa fa-car icon-blue"
                v-tooltip="$t('user.profile.menus.vehicle')"
                :icon-size="16"
                @click.prevent="manageVehicles"
            />
            <icon
                tag="a"
                class="fa fa-address-card"
                v-tooltip="$t('user.profile.menus.prospect_profile_setting')"
                :icon-size="16"
                @click.prevent="prospectProfileSetting"
            />
            <icon
                tag="a"
                class="fa fa-columns"
                v-tooltip="$t('user.profile.menus.prospects_table_setting')"
                :icon-size="16"
                @click.prevent="prospectsTableSetting"
            />
            <icon
                tag="a"
                class="fa fa-cog"
                v-tooltip="$t('user.profile.menus.user_profile_setting')"
                :icon-size="16"
                @click.prevent="setting"
            />
            <icon
                v-if="can('all')"
                tag="a"
                class="fa fa-sign-in icon-purple"
                color="#489f1f"
                v-tooltip="$t('user.profile.menus.use')"
                :icon-size="16"
                @click.prevent="impersonate"
            >
                <loading :loading="impersonating" />
            </icon>
            <icon
                tag="a"
                :class="['fa', openBlocs ? 'fa-caret-up' : 'fa-caret-down']"
                :icon-size="16"
                @click.prevent.stop="openBlocs = !openBlocs"
            />
        </div>
        <div id="hc-user-profile-body">
            <div id="hc-user-profile-body-columns">
                <div v-for="column in projectUserSettingsUserProfile">
                    <template v-for="bloc in column">
                        <profile-bloc :bloc="bloc" :open="openBlocs" />
                    </template>
                </div>
            </div>
        </div>

        <manage-user-tracker-stat-slide />
        <user-profile-setting-slide />
        <prospects-table-columns-slide />
        <prospect-profile-setting-slide />
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
    width: 25%;
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
    #hc-user-profile-body-columns > div {
        min-width: 100%;
        max-width: 100%;
    }
}
</style>

<script>
import { mapActions, mapGetters } from "vuex";
import store from "@/store";

import { OPEN_SLIDE } from "@/actions/slide";

import ManageUserTrackerStatSlide from "@/components/user/tracker/stat/Slide.vue";
import UserProfileSettingSlide from "./slides/setting/Slide.vue";
import ProspectsTableColumnsSlide from "@/components/prospect/table/slides/add-column/Slide.vue";
import ProspectProfileSettingSlide from "@/components/prospect/profile/slides/setting/Slide.vue";

import ProfileBloc from "./ProfileBloc";

export default {
    components: {
        ProfileBloc,

        ManageUserTrackerStatSlide,
        UserProfileSettingSlide,
        ProspectsTableColumnsSlide,
        ProspectProfileSettingSlide,
    },

    data() {
        return {
            impersonating: false,
            openBlocs: deviceType() == "desktop",
        };
    },

    methods: {
        ...mapActions({
            signIn: "auth/loginWithoutRedirect",
        }),

        manageFiles() {
            store.commit(OPEN_SLIDE, "user-manage-files");
        },

        duplicate() {
            store.commit(OPEN_SLIDE, "user-duplicate");
        },

        /**
         *
         */
        showConnections() {
            store.commit(OPEN_SLIDE, "user-logs-chart");
        },

        /**
         *
         */
        manageActions() {
            store.commit(OPEN_SLIDE, "user-manage-actions");
        },

        /**
         *
         */
        managePermissions() {
            store.commit(OPEN_SLIDE, "user-manage-permissions");
        },

        /**
         *
         */
        manageVehicles() {
            store.commit(OPEN_SLIDE, "user-manage-vehicles");
        },

        /**
         *
         */
        async impersonate() {
            this.impersonating = true;
            try {
                await axios.post(
                    "/api/project/" +
                        this.project.slug +
                        "/user/" +
                        this.user.id +
                        "/impersonate"
                );
                await this.signIn();
                store.commit("auth/SET_IMPERSONATING", true);
                store.commit("auth/SET_IMPERSONATE_PROJECT", this.project.slug);

                window.location.href =
                    "/project/" + this.project.slug + "/prospect";
            } catch (e) {
            } finally {
                this.impersonating = false;
            }
        },

        /**
         *
         */
        prospectProfileSetting() {
            store.commit(OPEN_SLIDE, "prospect-profile-setting");
        },

        /**
         *
         */
        prospectsTableSetting() {
            store.commit(OPEN_SLIDE, "prospects-table-columns-setting");
        },

        /**
         *
         */
        setting() {
            store.commit(OPEN_SLIDE, "user-profile-setting");
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "user",
            "folders",
            "projectUserSettingsUserProfile",
            "superAdmin",
            "can",
        ]),

        profileFields() {
            let fields = ["name", "phone_number", "email"];

            if (this.superAdmin) {
                fields.push("password");
            }

            return fields;
        },
    },
};
</script>
