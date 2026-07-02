<template>
    <div id="hc-prospect-profile">
        <div id="hc-prospect-profile-header">
            <div id="hc-prospect-profile-title-search">
                <div
                    id="hc-prospect-profile-title"
                    v-text="prospectFullName"
                ></div>
                <div id="hc-prospect-profile-search">
                    <search />
                </div>
            </div>
            <div id="hc-prospect-profile-menus">
                <icon
                    tag="a"
                    class="fa fa-comments icon-purple"
                    v-tooltip="$t('prospect.profile.menus.sms')"
                    :icon-size="16"
                    @click.prevent.stop="manageSms"
                />
                <icon
                    tag="a"
                    class="fa fa-phone icon-orange"
                    v-tooltip="$t('prospect.profile.menus.call')"
                    :icon-size="16"
                    @click.prevent.stop="manageInteractions"
                />
                <icon
                    tag="a"
                    class="fa fa-message icon-green"
                    v-tooltip="$t('prospect.profile.menus.message')"
                    :icon-size="16"
                    @click.prevent.stop="manageMessages"
                />
                <icon
                    tag="a"
                    class="fa fa-folder icon-blue"
                    v-tooltip="$t('prospect.profile.menus.file')"
                    :icon-size="16"
                    @click.prevent.stop="manageFiles"
                />
                <icon
                    tag="a"
                    class="fa fa-file-pdf icon-garnet"
                    v-tooltip="$t('prospect.profile.menus.document')"
                    :icon-size="16"
                    @click.prevent.stop="manageDocuments"
                />
                <icon
                    tag="a"
                    class="fa fa-shopping-cart icon-garnet"
                    v-tooltip="$t('prospect.profile.menus.order')"
                    :icon-size="16"
                    @click.prevent.stop="manageOrders"
                />
                <icon
                    tag="a"
                    class="fa fa-calendar icon-purple"
                    v-tooltip="$t('prospect.profile.menus.event')"
                    :icon-size="16"
                    @click.prevent.stop="manageEvents"
                />
                <icon
                    tag="a"
                    class="fa fa-tags icon-purple"
                    v-tooltip="$t('prospect.profile.menus.label')"
                    :icon-size="16"
                    @click.prevent.stop="manageLabels"
                />
                <icon
                    tag="a"
                    class="fa fa-clipboard icon-blue"
                    v-tooltip="$t('prospect.profile.menus.questionnaire')"
                    :icon-size="16"
                    @click.prevent.stop="manageQuestionnaires"
                />
                <!--icon
                    tag="a"
                    class="fa fa-sign-in-alt icon-blue"
                    v-tooltip="
                        'Envoyer un email de connexion vers l\'espace client'
                    "
                    :icon-size="16"
                    @click.prevent.stop="sendAuthEmail"
                >
                    <loading :loading="sendingAuthEmail" />
                </icon-->
                <icon
                    tag="a"
                    class="fa fa-pen icon-cyan"
                    v-tooltip="$t('prospect.profile.menus.revision')"
                    :icon-size="16"
                    @click.prevent.stop="showRevisions"
                />
                <icon
                    tag="a"
                    class="fa fa-eye icon-blue"
                    v-tooltip="$t('prospect.profile.menus.log')"
                    :icon-size="16"
                    @click.prevent.stop="showLogs"
                />
                <icon
                    v-if="prospect.latitude && prospect.longitude"
                    tag="a"
                    class="fa fa-map-marker icon-garnet"
                    v-tooltip="$t('prospect.profile.menus.map')"
                    :icon-size="16"
                    @click.prevent.stop="map"
                />
                <icon
                    v-if="prospect.latitude && prospect.longitude"
                    tag="a"
                    v-tooltip="'Google MAP'"
                    :icon-size="16"
                    :href="googleMapURL"
                    target="_blank"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 92.3 132.3"
                    >
                        <path
                            fill="#1a73e8"
                            d="M60.2 2.2C55.8.8 51 0 46.1 0 32 0 19.3 6.4 10.8 16.5l21.8 18.3L60.2 2.2z"
                        />
                        <path
                            fill="#ea4335"
                            d="M10.8 16.5C4.1 24.5 0 34.9 0 46.1c0 8.7 1.7 15.7 4.6 22l28-33.3-21.8-18.3z"
                        />
                        <path
                            fill="#4285f4"
                            d="M46.2 28.5c9.8 0 17.7 7.9 17.7 17.7 0 4.3-1.6 8.3-4.2 11.4 0 0 13.9-16.6 27.5-32.7-5.6-10.8-15.3-19-27-22.7L32.6 34.8c3.3-3.8 8.1-6.3 13.6-6.3"
                        />
                        <path
                            fill="#fbbc04"
                            d="M46.2 63.8c-9.8 0-17.7-7.9-17.7-17.7 0-4.3 1.5-8.3 4.1-11.3l-28 33.3c4.8 10.6 12.8 19.2 21 29.9l34.1-40.5c-3.3 3.9-8.1 6.3-13.5 6.3"
                        />
                        <path
                            fill="#34a853"
                            d="M59.1 109.2c15.4-24.1 33.3-35 33.3-63 0-7.7-1.9-14.9-5.2-21.3L25.6 98c2.6 3.4 5.3 7.3 7.9 11.3 9.4 14.5 6.8 23.1 12.8 23.1s3.4-8.7 12.8-23.2"
                        />
                    </svg>
                </icon>
                <icon
                    v-if="address"
                    :size="30"
                    v-tooltip="'Waze'"
                    tag="a"
                    @click.stop
                    :href="wazeMapURL"
                >
                    <svg viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M27.5,2.674C21.319,2.674 15.556,5.451 11.667,10.382C8.958,13.854 7.5,18.16 7.5,22.535L7.5,26.215C7.5,27.812 6.875,29.34 5.833,30.451C5,31.285 3.958,31.91 2.847,32.188C3.264,33.229 4.236,34.826 5.972,36.563C7.431,38.09 9.167,39.34 11.042,40.243L11.042,40.174C12.222,38.368 14.167,37.326 16.319,37.326C16.736,37.326 17.083,37.396 17.5,37.465C20,37.951 21.944,39.896 22.431,42.326L27.569,42.326C32.917,42.326 37.986,40.104 41.667,36.493C47.361,30.799 49.097,22.257 45.972,14.896C42.847,7.465 35.625,2.674 27.5,2.674Z"
                            style="fill: white"
                        />
                        <path
                            d="M27.5,0.174C20.625,0.174 14.167,3.229 9.792,8.715C6.667,12.674 5,17.535 5,22.604L5,26.215C5,28.09 3.681,29.826 1.111,29.965C0.486,29.965 0,30.451 -0.069,31.076C-0.139,32.743 1.667,35.868 4.167,38.368C5.903,40.104 7.917,41.493 10.069,42.604C9.375,46.424 12.361,49.896 16.25,49.896L16.319,49.896C19.306,49.896 21.806,47.813 22.431,44.965L27.639,44.965C28.194,47.813 30.694,49.896 33.75,49.896C34.444,49.896 35.208,49.757 35.903,49.549C37.639,48.993 38.958,47.674 39.583,45.937C40.139,44.34 40.069,42.743 39.583,41.424C40.972,40.521 42.222,39.549 43.403,38.368C47.639,34.201 50,28.507 50,22.604C50,16.632 47.639,11.076 43.403,6.84C39.167,2.465 33.472,0.174 27.5,0.174ZM27.5,2.674C35.556,2.674 42.847,7.535 45.972,14.965C49.097,22.396 47.361,30.937 41.667,36.563C37.986,40.243 32.917,42.396 27.569,42.396L22.431,42.396C21.944,39.896 20,38.021 17.5,37.535C17.083,37.465 16.736,37.396 16.319,37.396C14.236,37.396 12.222,38.438 11.042,40.243L11.042,40.313C9.167,39.34 7.5,38.09 5.972,36.632C4.236,34.896 3.264,33.229 2.847,32.257C4.028,31.979 5,31.354 5.833,30.521C6.875,29.34 7.5,27.882 7.5,26.285L7.5,22.604C7.5,18.229 8.958,13.924 11.667,10.451C15.556,5.382 21.319,2.674 27.5,2.674Z"
                        />
                        <path
                            d="M37.5,15.035C36.111,15.035 35,16.146 35,17.535C35,18.924 36.111,20.035 37.5,20.035C38.889,20.035 40,18.924 40,17.535C40,16.146 38.889,15.035 37.5,15.035Z"
                        />
                        <path
                            d="M22.5,15.035C21.111,15.035 20,16.146 20,17.535C20,18.924 21.111,20.035 22.5,20.035C23.889,20.035 25,18.924 25,17.535C25,16.146 23.889,15.035 22.5,15.035Z"
                        />
                        <path
                            d="M22.083,24.965C21.181,24.965 20.556,25.868 20.972,26.701C22.639,30.174 26.111,32.396 30,32.396C33.889,32.396 37.361,30.174 39.028,26.701C39.375,25.868 38.819,24.965 37.917,24.965C37.431,24.965 37.014,25.243 36.806,25.66C35.556,28.229 32.917,29.896 30.069,29.896C27.153,29.896 24.514,28.229 23.333,25.66C23.056,25.243 22.639,24.965 22.083,24.965Z"
                        />
                    </svg>
                </icon>
                <icon
                    tag="a"
                    class="fa fa-trash icon-red"
                    v-tooltip="$t('delete')"
                    :icon-size="16"
                    @click.prevent.stop="remove"
                >
                    <loading :loading="removing" />
                </icon>
                <icon
                    v-if="can('all.prospect.setting-prospect-profile')"
                    tag="a"
                    class="fa fa-cog"
                    v-tooltip="$t('prospect.profile.menus.setting')"
                    :icon-size="16"
                    @click.prevent.stop="setting"
                />
                <icon
                    tag="a"
                    :class="['fa', openBlocs ? 'fa-caret-up' : 'fa-caret-down']"
                    :icon-size="16"
                    @click.prevent.stop="openBlocs = !openBlocs"
                />
            </div>
        </div>
        <div id="hc-prospect-profile-body">
            <div id="hc-prospect-profile-body-columns">
                <draggable
                    v-for="(column, i) in projectUserSettingsProspectProfile"
                    tag="div"
                    :list="column"
                    :disabled="!movable"
                    group="prospect-profile-bloc"
                    item-key="type"
                    handle=".bloc-handle"
                    @end="setBlocColumn"
                >
                    <template #item="{ element, index }">
                        <profile-bloc :bloc="element" :open="openBlocs" />
                    </template>
                    <template #footer>
                        <div
                            class="hc-prospect-profile-body-columns-bloc-add"
                            @click.stop="addBloc(i)"
                        ></div>
                    </template>
                </draggable>
            </div>
        </div>
        <prospect-profile-setting-slide />
    </div>
</template>

<style>
#hc-prospect-profile {
    width: 100%;
    display: flex;
    flex-direction: column;
    height: 100%;
}

#hc-prospect-profile-header {
    display: flex;
    flex-direction: row;
    align-items: center;
}

#hc-prospect-profile-title-search {
    display: flex;
    flex-direction: row;
    align-items: center;
    flex: 1;
}

#hc-prospect-profile-title {
    padding: 15px 0 15px 15px;
    font-weight: bold;
    font-size: 15px;
}

#hc-prospect-profile-search {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 10px 20px;
    background-color: #f8fafc;
}

#hc-prospect-profile-menus {
    display: flex;
    flex-direction: row;
    align-items: center;
}

#hc-prospect-profile-body {
    flex: 1;
    overflow: auto;
    display: flex;
    flex-direction: row;
    justify-content: center;
    gap: 15px;
    padding: 0 20px;
}

#hc-prospect-profile-body-columns {
    width: 1400px;
    max-width: 100%;
    display: flex;
    flex-direction: row;
    padding: 20px 0;
}

#hc-prospect-profile-body-columns > div {
    flex: 1;
    display: flex;
    flex-direction: column;
    width: 25%;
    padding: 7px;
    gap: 15px;
}

.hc-prospect-profile-body-columns-bloc-add {
    width: 100%;
    height: 100px;
    border-radius: 10px;
    border: 1px dashed #aaa;
    background-color: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.hc-prospect-profile-body-columns-bloc-add:hover {
    background-color: #eee;
}

.hc-prospect-profile-body-columns-bloc-add:before {
    content: "+";
    font-size: 40px;
    font-weight: bold;
    color: #ccc;
}

@media (max-width: 767px) {
    #hc-prospect-profile {
        overflow: auto;
    }

    #hc-prospect-profile-body-columns {
        flex-direction: column;
    }

    #hc-prospect-profile-body-columns > div {
        width: 100%;
    }

    #hc-prospect-profile-header {
        flex-direction: column;
        padding-top: 54px;
    }

    #hc-prospect-profile-title-search {
        width: 100%;
        position: absolute;
        top: 0;
        background-color: #ffffff;
        z-index: 1;
    }

    #hc-prospect-profile-title {
        padding: 7px;
        max-width: 50%;
    }

    #hc-prospect-profile-search {
    }

    #hc-prospect-profile-body {
        overflow: visible;
    }

    #hc-prospect-profile-menus {
        width: 100%;
    }

    .hc-prospect-profile-body-columns-bloc-add {
        display: none;
    }
}
</style>

<script>
import ApiService from "@/apis/api.service";
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_SLIDE, OPEN_LEFT_SLIDE } from "@/actions/slide";
import { SET_PROSPECT_ORDER_TAB } from "@/actions/project/prospect/order";
import {
    GET_PROJECT_USER_SETTING,
    UPDATE_PROJECT_USER_SETTING,
} from "@/actions/project/user/setting";
import { SET_EVENT } from "@/actions/project/event";
import {
    REMOVE_PROSPECT,
    SET_PROSPECT,
    SET_PROSPECT_CATEGORY,
} from "@/actions/project/prospect";
import { SET_INTERACTION_PROSPECT } from "@/actions/project/prospect/interaction";
import { SET_USER } from "@/actions/project/user";
import {
    SET_PROSPECT_PROFILE_SETTING_COLUMN_TAB,
    SET_PROSPECT_PROFILE_SETTING_BLOC_TAB,
} from "@/actions/project/prospect";

import Search from "./Search.vue";
import ProfileBloc from "./ProfileBloc.vue";
import ProspectProfileSettingSlide from "./slides/setting/Slide.vue";

export default {
    components: {
        Search,
        ProfileBloc,
        ProspectProfileSettingSlide,
    },

    created() {
        this.getSetting();
    },

    data() {
        return {
            removing: false,
            movable: deviceType() == "desktop",
            openBlocs: deviceType() == "desktop",
            sendingAuthEmail: false,
        };
    },

    methods: {
        /**
         * Get user columns
         */
        getSetting() {
            store.dispatch(GET_PROJECT_USER_SETTING, {
                key: "prospect-profile",
            });
        },

        /**
         * Manage prospect documents
         * See: @/components/prospect/document/Slide.vue
         */
        manageDocuments() {
            store.commit(OPEN_SLIDE, "prospect-manage-documents");
        },

        /**
         * Manage prospect files
         * See: @/components/prospect/file/Slide.vue
         */
        manageFiles() {
            store.commit(OPEN_SLIDE, "prospect-manage-files");
        },

        /**
         * Manage prospect messages
         * See: @/components/prospect/message/Slide.vue
         */
        manageMessages() {
            store.commit(OPEN_SLIDE, "prospect-manage-messages");
        },

        /**
         * Manage prospect orders
         * See: @/components/prospect/order/Slide.vue
         */
        manageOrders() {
            store.commit(SET_PROSPECT_ORDER_TAB, 0);
            store.commit(OPEN_SLIDE, "prospect-manage-orders");
        },

        /**
         * Manage prospect events
         * See: @/components/prospect/event/Slide.vue
         */
        manageEvents() {
            const startDate = new Date();
            startDate.setMinutes(60);

            const endDate = new Date();
            endDate.setMinutes(90);

            store.commit(SET_PROSPECT, this.prospect);
            store.commit(SET_EVENT, {
                prospect: this.prospect,
                user: this.$store.state.auth.user,
                started_at: dateToString(startDate),
                ended_at: dateToString(endDate),
            });
            store.commit(OPEN_SLIDE, "prospect-manage-events");
        },

        /**
         * Manage prospect labels
         * See: @/components/prospect/label/Slide.vue
         */
        manageLabels() {
            store.commit(SET_PROSPECT_CATEGORY, null);
            store.commit(OPEN_SLIDE, "prospect-manage-labels");
        },

        /**
         * Manage prospect interactions
         * See: @/components/prospect/interaction/Slide.vue
         */
        manageInteractions() {
            store.commit(SET_INTERACTION_PROSPECT, this.prospect);
            store.commit(OPEN_LEFT_SLIDE, "prospect-manage-interactions");
        },

        /**
         * Manage prospect sms
         * See: @/components/prospect/sms/Slide.vue
         */
        manageSms() {
            store.commit(OPEN_SLIDE, "prospect-manage-sms");
        },

        /**
         * Manage prospect forms
         * See: @/components/prospect/sms/Slide.vue
         */
        manageQuestionnaires() {
            store.commit(OPEN_SLIDE, "prospect-manage-questionnaires");
        },

        /**
         * Send authentication email
         */
        async sendAuthEmail() {
            this.sendingAuthEmail = true;

            try {
                await ApiService.get(
                    `project/${this.project.slug}/prospect/${this.prospect.id}/auth`
                );
            } finally {
                this.sendingAuthEmail = false;
            }
        },

        /**
         *
         */
        showRevisions() {
            store.commit(OPEN_SLIDE, "prospect-manage-revisions");
        },

        /**
         *
         */
        showLogs() {
            store.commit(OPEN_SLIDE, "prospect-manage-logs");
        },

        /**
         *
         */
        map() {
            this.$router.push({
                name: "map",
                params: {
                    project: this.project.slug,
                },
                query: {
                    filters: JSON.stringify({
                        validAddress: 1,
                        position: [
                            this.prospect.latitude,
                            this.prospect.longitude,
                            30,
                        ].join(","),
                    }),
                },
            });
        },

        /**
         *
         */
        async remove() {
            this.removing = true;

            try {
                await store.dispatch(REMOVE_PROSPECT, this.prospect.id);

                this.$router.push({
                    name: "prospect",
                    params: {
                        project: this.project.slug,
                    },
                });
            } finally {
                this.removing = false;
            }
        },

        /**
         *
         */
        setting() {
            store.commit(SET_USER, this.user);
            store.commit(OPEN_SLIDE, "prospect-profile-setting");
        },

        setBlocColumn(e) {
            let newSettings = [...this.projectUserSettingsProspectProfile];

            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "prospect-profile",
                value: newSettings,
            });
        },

        addBloc(column) {
            store.commit(OPEN_SLIDE, "prospect-profile-setting");
            store.commit(SET_USER, this.user);
            store.commit(SET_PROSPECT_PROFILE_SETTING_COLUMN_TAB, column);
            store.commit(SET_PROSPECT_PROFILE_SETTING_BLOC_TAB, 1);
        },
    },

    computed: {
        ...mapGetters("auth", ["user"]),

        ...mapGetters([
            "project",
            "prospect",
            "categories",
            "threads",
            "folders",
            "prospectFullName",
            "projectUserSettingsProspectProfile",
            "can",
        ]),

        address() {
            return [
                this.prospect.street,
                this.prospect.postal_code,
                this.prospect.city,
                this.prospect.country,
            ]
                .filter((a) => a)
                .join(" ");
        },

        googleMapURL: function () {
            return (
                "https://www.google.com/maps/search/?api=1&query=" +
                encodeURI(this.address)
            );
        },

        wazeMapURL() {
            return "https://waze.com/ul?q=" + encodeURI(this.address);
        },
    },
};
</script>
