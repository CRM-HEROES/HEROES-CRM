<template>
    <item-list
        class="hc-flex-1"
        style="height: 100%; overflow: auto"
        padding="5px"
    >
        <!-- Impersonating -->
        <item
            v-if="impersonating"
            style="color: #ffffff; background-color: #7939b8"
            tag="a"
            @click.prevent="leaveImpersonation"
        >
            <icon class="fa fa-sign-out" color="#FFFFFF" />
            <div
                class="hc-item-main-content"
                v-text="$t('auth.impersonate.back_to_my_account')"
            ></div>
            <icon class="fa fa-caret-right" color="#FFFFFF" />
            <loading :loading="leavingImpersonation" />
        </item>

        <!-- Authenticated user -->
        <item class="hc-setting-item">
            <icon class="fa fa-user" />
            <component
                :is="project ? 'router-link' : 'div'"
                :to="
                    project
                        ? {
                              name: 'user.show',
                              params: {
                                  user: this.user.id,
                                  project: project.slug,
                              },
                          }
                        : null
                "
                class="hc-item-main-content hc-flex-column"
                style="text-decoration: none; color: #000000"
            >
                <div class="hc-setting-item-name" v-text="userFullName"></div>
                <div
                    class="hc-setting-item-description"
                    v-text="user.email"
                ></div>
            </component>
            <icon
                v-if="!impersonating"
                class="fa fa-sign-out"
                @click.prevent.stop="logout"
            />
        </item>

        <!-- Locale -->
        <item class="hc-setting-item" v-if="locale" @click="manageLocale">
            <icon><div v-html="locale.flag"></div></icon>
            <div class="hc-item-main-content hc-flex-column">
                <div
                    class="hc-setting-item-name"
                    v-text="$t('setting.locale.title')"
                ></div>
                <div
                    class="hc-setting-item-description"
                    v-text="locale.name"
                ></div>
            </div>
            <icon class="fa fa-caret-right" />
        </item>

        <!-- Project Setting -->
        <item
            v-if="project && can('all.project')"
            class="hc-setting-item"
            tag="router-link"
            :to="{
                name: 'project.show',
                params: {
                    project: project.slug,
                },
            }"
        >
            <icon>
                <img
                    :src="
                        project.thumbnail
                            ? project.thumbnail
                            : '/images/logo.png'
                    "
                    :alt="project.name"
                />
            </icon>
            <div class="hc-item-main-content hc-flex-column">
                <div
                    class="hc-setting-item-name"
                    v-text="
                        $t('setting.project.title', {
                            project: project.name,
                        })
                    "
                ></div>
                <div
                    class="hc-setting-item-description"
                    v-text="$t('setting.project.description')"
                ></div>
            </div>
        </item>

        <!-- Google authenticator -->
        <item
            class="hc-setting-item"
            @click="toggleGoogleAuthenticator"
            tag="a"
        >
            <icon class="fa fa-power-off" />
            <div class="hc-item-main-content hc-flex-column">
                <div
                    class="hc-setting-item-name"
                    v-text="$t('setting.google_auth.title')"
                ></div>
                <div
                    class="hc-setting-item-description"
                    v-text="
                        googleAuthenticator ? $t('enabled') : $t('disabled')
                    "
                ></div>
            </div>
            <icon
                v-if="googleAuthenticator !== undefined"
                class="fa fa-lock"
                :color="googleAuthenticator ? 'green' : 'grey'"
            />
            <loading :loading="removingGoogleAuthenticator" />
        </item>

        <!-- Google Drive -->
        <item
            v-if="project && can('all.project')"
            class="hc-setting-item"
            @click="toggleGoogleDriveAccount"
        >
            <icon
                ><svg viewBox="0 0 87.3 78" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="m6.6 66.85 3.85 6.65c.8 1.4 1.95 2.5 3.3 3.3l13.75-23.8h-27.5c0 1.55.4 3.1 1.2 4.5z"
                        fill="#0066da"
                    />
                    <path
                        d="m43.65 25-13.75-23.8c-1.35.8-2.5 1.9-3.3 3.3l-25.4 44a9.06 9.06 0 0 0 -1.2 4.5h27.5z"
                        fill="#00ac47"
                    />
                    <path
                        d="m73.55 76.8c1.35-.8 2.5-1.9 3.3-3.3l1.6-2.75 7.65-13.25c.8-1.4 1.2-2.95 1.2-4.5h-27.502l5.852 11.5z"
                        fill="#ea4335"
                    />
                    <path
                        d="m43.65 25 13.75-23.8c-1.35-.8-2.9-1.2-4.5-1.2h-18.5c-1.6 0-3.15.45-4.5 1.2z"
                        fill="#00832d"
                    />
                    <path
                        d="m59.8 53h-32.3l-13.75 23.8c1.35.8 2.9 1.2 4.5 1.2h50.8c1.6 0 3.15-.45 4.5-1.2z"
                        fill="#2684fc"
                    />
                    <path
                        d="m73.4 26.5-12.7-22c-.8-1.4-1.95-2.5-3.3-3.3l-13.75 23.8 16.15 28h27.45c0-1.55-.4-3.1-1.2-4.5z"
                        fill="#ffba00"
                    />
                </svg>
            </icon>
            <div class="hc-item-main-content hc-flex-column">
                <div
                    class="hc-setting-item-name"
                    v-text="$t('setting.google_drive.title')"
                ></div>
                <div
                    class="hc-setting-item-description"
                    v-text="
                        googleDrive
                            ? googleDrive.name
                            : $t('setting.google_drive.description')
                    "
                ></div>
            </div>
            <icon v-if="googleDrive" class="fa fa-check" :color="'green'" />
        </item>

        <!-- Google Calendar -->
        <item class="hc-setting-item" @click="toggleGoogleCalendarAccount">
            <icon>
                <svg
                    version="1.1"
                    xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink"
                    x="0px"
                    y="0px"
                    viewBox="0 0 200 200"
                    enable-background="new 0 0 200 200"
                    xml:space="preserve"
                >
                    <g>
                        <g transform="translate(3.75 3.75)">
                            <path
                                fill="#FFFFFF"
                                d="M148.882,43.618l-47.368-5.263l-57.895,5.263L38.355,96.25l5.263,52.632l52.632,6.579l52.632-6.579 l5.263-53.947L148.882,43.618z"
                            ></path>
                            <path
                                fill="#1A73E8"
                                d="M65.211,125.276c-3.934-2.658-6.658-6.539-8.145-11.671l9.132-3.763c0.829,3.158,2.276,5.605,4.342,7.342 c2.053,1.737,4.553,2.592,7.474,2.592c2.987,0,5.553-0.908,7.697-2.724s3.224-4.132,3.224-6.934c0-2.868-1.132-5.211-3.395-7.026 s-5.105-2.724-8.5-2.724h-5.276v-9.039H76.5c2.921,0,5.382-0.789,7.382-2.368c2-1.579,3-3.737,3-6.487 c0-2.447-0.895-4.395-2.684-5.855s-4.053-2.197-6.803-2.197c-2.684,0-4.816,0.711-6.395,2.145s-2.724,3.197-3.447,5.276 l-9.039-3.763c1.197-3.395,3.395-6.395,6.618-8.987c3.224-2.592,7.342-3.895,12.342-3.895c3.697,0,7.026,0.711,9.974,2.145 c2.947,1.434,5.263,3.421,6.934,5.947c1.671,2.539,2.5,5.382,2.5,8.539c0,3.224-0.776,5.947-2.329,8.184 c-1.553,2.237-3.461,3.947-5.724,5.145v0.539c2.987,1.25,5.421,3.158,7.342,5.724c1.908,2.566,2.868,5.632,2.868,9.211 s-0.908,6.776-2.724,9.579c-1.816,2.803-4.329,5.013-7.513,6.618c-3.197,1.605-6.789,2.421-10.776,2.421 C73.408,129.263,69.145,127.934,65.211,125.276z"
                            ></path>
                            <path
                                fill="#1A73E8"
                                d="M121.25,79.961l-9.974,7.25l-5.013-7.605l17.987-12.974h6.895v61.197h-9.895L121.25,79.961z"
                            ></path>
                            <path
                                fill="#EA4335"
                                d="M148.882,196.25l47.368-47.368l-23.684-10.526l-23.684,10.526l-10.526,23.684L148.882,196.25z"
                            ></path>
                            <path
                                fill="#34A853"
                                d="M33.092,172.566l10.526,23.684h105.263v-47.368H43.618L33.092,172.566z"
                            ></path>
                            <path
                                fill="#4285F4"
                                d="M12.039-3.75C3.316-3.75-3.75,3.316-3.75,12.039v136.842l23.684,10.526l23.684-10.526V43.618h105.263 l10.526-23.684L148.882-3.75H12.039z"
                            ></path>
                            <path
                                fill="#188038"
                                d="M-3.75,148.882v31.579c0,8.724,7.066,15.789,15.789,15.789h31.579v-47.368H-3.75z"
                            ></path>
                            <path
                                fill="#FBBC04"
                                d="M148.882,43.618v105.263h47.368V43.618l-23.684-10.526L148.882,43.618z"
                            ></path>
                            <path
                                fill="#1967D2"
                                d="M196.25,43.618V12.039c0-8.724-7.066-15.789-15.789-15.789h-31.579v47.368H196.25z"
                            ></path>
                        </g>
                    </g></svg
            ></icon>
            <div class="hc-item-main-content hc-flex-column">
                <div
                    class="hc-setting-item-name"
                    v-text="$t('setting.google_calendar.title')"
                ></div>
                <div
                    class="hc-setting-item-description"
                    v-text="
                        googleCalendar
                            ? googleCalendar.name
                            : $t('setting.google_calendar.description')
                    "
                ></div>
            </div>
            <icon v-if="googleCalendar" class="fa fa-check" :color="'green'" />
        </item>

        <!-- Google Contact -->
        <item class="hc-setting-item" @click="toggleGoogleContactAccount">
            <icon>
                <svg
                    version="1.1"
                    xmlns="http://www.w3.org/2000/svg"
                    x="0px"
                    y="0px"
                    viewBox="0 0 423 500"
                    enable-background="new 0 0 423 500"
                >
                    <path
                        fill="#86A9FF"
                        d="M160.256,243.59C71.667,243.59,0,315.256,0,403.846v67.308C0,487.083,12.917,500,28.846,500h76.923 l76.923-256.41H160.256z"
                    />
                    <path
                        fill="#578CFF"
                        d="M423.077,349.359c0-58.462-47.308-105.769-105.769-105.769h-76.923V500h76.923 c58.462,0,105.769-47.308,105.769-105.769"
                    />
                    <path
                        fill="#0057CC"
                        d="M76.923,349.359c0-58.462,47.308-105.769,105.769-105.769h57.692c58.462,0,105.769,47.308,105.769,105.769 v44.872c0,58.462-47.308,105.769-105.769,105.769H105.769c-15.929,0-28.846-12.917-28.846-28.846V349.359z"
                    />
                    <circle
                        fill="#0057CC"
                        cx="211.538"
                        cy="99.359"
                        r="99.359"
                    />
                </svg>
            </icon>
            <div class="hc-item-main-content hc-flex-column">
                <div
                    class="hc-setting-item-name"
                    v-text="$t('setting.google_contact.title')"
                ></div>
                <div
                    class="hc-setting-item-description"
                    v-text="
                        googleContact
                            ? googleContact.name
                            : $t('setting.google_contact.description')
                    "
                ></div>
            </div>
            <icon v-if="googleContact" class="fa fa-check" :color="'green'" />
        </item>

        <!-- Pipedrive -->
        <!--item
                v-if="project && can('all.project')"
                class="hc-setting-item"
                @click="managePipedriveApi"
            >
                <icon>
                    <svg viewBox="0 0 32 32">
                        <path
                            d="M27.75,32H4.25C1.9,32,0,30.1,0,27.75V4.25C0,1.9,1.9,0,4.25,0h23.5C30.1,0,32,1.9,32,4.25v23.5C32,30.1,30.1,32,27.75,32z"
                        />
                        <path
                            style="fill: #ffffff"
                            d="M17.12,7.33c-2.01,0-3.17,0.91-3.73,1.52c-0.07-0.54-0.42-1.23-1.81-1.23H8.55v3.14h1.23 c0.2,0,0.27,0.07,0.27,0.27V25.4h3.58v-5.37v-0.41c0.56,0.51,1.62,1.22,3.29,1.22c3.5,0,5.94-2.77,5.94-6.75 C22.89,10.05,20.56,7.33,17.12,7.33 M16.39,17.72c-1.93,0-2.8-1.84-2.8-3.56c0-2.7,1.47-3.66,2.85-3.66c1.69,0,2.84,1.45,2.84,3.63 C19.26,16.62,17.81,17.72,16.39,17.72"
                        /></svg
                ></icon>
                <div class="hc-item-main-content hc-flex-column">
                    <div
                        class="hc-setting-item-name"
                        v-text="$t('setting.pipedrive.title')"
                    ></div>
                    <div
                        class="hc-setting-item-description"
                        v-text="
                            googleCalendar
                                ? googleCalendar.name
                                : $t('setting.pipedrive.description')
                        "
                    ></div>
                </div>
                <icon
                    v-if="googleCalendar"
                    class="fa fa-check"
                    :color="'green'"
                />
            </item-->

        <!-- Project users list -->
        <item
            v-if="project && can('all.project')"
            class="hc-setting-item"
            tag="router-link"
            :to="{
                name: 'user',
                params: {
                    project: project.slug,
                },
            }"
        >
            <icon class="fa fa-users" />
            <div class="hc-item-main-content hc-flex-column">
                <div
                    class="hc-setting-item-name"
                    v-text="$t('setting.users.title')"
                ></div>
                <div
                    class="hc-setting-item-description"
                    v-text="
                        $t('setting.users.description', {
                            project: project.name,
                        })
                    "
                ></div>
            </div>
        </item>

        <!-- Files -->
        <item v-if="project" class="hc-setting-item" @click="manageFiles">
            <icon class="fa fa-folder icon-blue" />
            <div class="hc-item-main-content hc-flex-column">
                <div
                    class="hc-setting-item-name"
                    v-text="'Gérer mes fichiers'"
                ></div>
            </div>
            <icon class="fa fa-caret-right" />
        </item>

        <!-- Pipeline -->
        <item
            v-if="project"
            class="hc-setting-item"
            tag="router-link"
            :to="{
                name: 'pipeline',
                params: {
                    project: project.slug,
                },
            }"
        >
            <icon class="fa fa-forward" />
            <div class="hc-item-main-content hc-flex-column">
                <div class="hc-setting-item-name" v-text="'Pipeline'"></div>
            </div>
        </item>

        <!-- Sessions -->
        <item class="hc-setting-item" tag="a" @click.prevent="manageSessions">
            <icon class="fa fa-desktop" />
            <div class="hc-item-main-content hc-flex-column">
                <div
                    class="hc-setting-item-name"
                    v-text="$t('setting.devices.title')"
                ></div>
                <div
                    class="hc-setting-item-description"
                    v-text="$t('setting.devices.description')"
                ></div>
            </div>
            <icon class="fa fa-caret-right" />
        </item>

        <!-- Trash -->
        <item class="hc-setting-item" tag="a" @click.prevent="manageTrash">
            <icon class="fa fa-trash icon-blue" />
            <div class="hc-item-main-content hc-flex-column">
                <div
                    class="hc-setting-item-name"
                    v-text="$t('setting.trash.title')"
                ></div>
                <div
                    class="hc-setting-item-description"
                    v-text="$t('setting.trash.description')"
                ></div>
            </div>
            <icon class="fa fa-caret-right" />
        </item>

        <!-- Menu icon -->
        <item class="hc-setting-item" tag="a" @click.prevent="manageMenuIcon">
            <icon class="fa fa-icons" />
            <div class="hc-item-main-content hc-flex-column">
                <div
                    class="hc-setting-item-name"
                    v-text="$t('setting.menu_icons.title')"
                ></div>
                <div
                    class="hc-setting-item-description"
                    v-text="$t('setting.menu_icons.description')"
                ></div>
            </div>
            <icon class="fa fa-caret-right" />
        </item>

        <!-- Tutorial -->
        <item class="hc-setting-item" @click="manageTutorials">
            <icon class="fa fa-info" />
            <div
                class="hc-item-main-content hc-flex-column"
                v-text="$t('setting.tutorials.title')"
            ></div>
            <icon class="fa fa-caret-right" />
        </item>

        <!-- Event reminder -->
        <item class="hc-setting-item" tag="label">
            <icon class="fa fa-bell" />
            <div
                class="hc-item-main-content hc-flex-column"
                v-text="$t('setting.events_reminder.title')"
            ></div>
            <checkbox v-model="remindEvent" />
        </item>

        <!-- Pappers -->
        <item class="hc-setting-item" @click="managePappers">
            <icon class="fa fa-users" />
            <div
                class="hc-item-main-content hc-flex-column"
                v-text="'Analyser des données grâce à l\'IA'"
            ></div>
            <icon class="fa fa-caret-right" />
        </item>
    </item-list>

    <!-- Copyright -->
    <div id="hc-setting-copyright">
        <div v-text="$t('setting.copyright.title')"></div>
        <div>
            {{ $t("setting.copyright.technical_questions") }}:
            <a href="mailto:support@heroescrm.pro">support@heroescrm.pro</a>
        </div>
        <div>
            {{ $t("setting.copyright.commercial_questions") }}:
            <a href="mailto:contact@heroescrm.pro">contact@heroescrm.pro</a>
        </div>
    </div>
</template>

<style>
.hc-setting-item {
    padding: 5px !important;
}

.hc-setting-item-name {
    font-size: 12px;
}

.hc-setting-item-description {
    font-size: 11px;
    color: #888888;
}

#hc-setting-copyright {
    padding: 15px;
    color: #888;
    text-align: center;
    font-size: 11px;
}
</style>

<script>
import { mapActions, mapGetters } from "vuex";
import store from "@/store";
import locales from "@/utils/locale";

import { UPDATE_PROJECT_USER_SETTING } from "@/actions/project/user/setting";
import { OPEN_SUB_SLIDE } from "@/actions/slide";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_USER } from "@/actions/project/user";

import GoogleCalendarService from "@/apis/google/calendar";
import GoogleContactService from "@/apis/google/contact";
import GoogleDriveService from "@/apis/project/google/drive";
import GoogleAuthenticatorService from "@/apis/google/authenticator";

export default {
    data() {
        return {
            leavingImpersonation: false,

            googleCalendar: undefined,
            removingGoogleCalendar: false,

            googleContact: undefined,
            removingGoogleContact: false,

            googleAuthenticator: undefined,
            googleDrive: undefined,
            removingGoogleDrive: false,

            removingGoogleAuthenticator: false,
        };
    },

    created() {
        this.checkGoogleDriveAccount();
        this.checkGoogleCalendarAccount();
        this.checkGoogleContactAccount();
        this.checkGoogleAuthenticator();
    },

    methods: {
        ...mapActions({
            signIn: "auth/login",
            signOut: "auth/logout",
        }),

        async leaveImpersonation() {
            this.leavingImpersonation = true;
            try {
                await axios.post("/api/user/impersonate/leave");
                await this.signIn();
                store.commit("auth/SET_IMPERSONATING", false);

                this.$router.push({
                    name: "dashboard",
                });
            } catch (e) {
            } finally {
                this.leavingImpersonation = false;
            }
        },

        /**
         *
         */
        manageFiles() {
            store.commit(SET_USER, this.user);
            store.commit(OPEN_SUB_SLIDE, "user-manage-files");
        },

        /**
         *
         */
        manageSessions() {
            store.commit(OPEN_SUB_SLIDE, "user-manage-sessions");
        },

        /**
         *
         */
        manageTrash() {
            store.commit(OPEN_SUB_SLIDE, "trash");
        },

        /**
         *
         */
        manageMenuIcon() {
            store.commit(OPEN_SUB_SLIDE, "menu-icon");
        },

        async logout() {
            await axios
                .post("/logout")
                .then(({ data }) => {
                    this.signOut();
                })
                .catch(({ response: { data } }) => {
                    alert(data.message);
                })
                .finally(() => {});
        },

        /**
         *
         */
        async checkGoogleCalendarAccount() {
            if (this.googleCalendar === undefined) {
                const { data } = await GoogleCalendarService.get();
                this.googleCalendar = data;
            }
        },

        /**
         *
         */
        async checkGoogleContactAccount() {
            if (this.googleContact === undefined) {
                const { data } = await GoogleContactService.get();
                this.googleContact = data;
            }
        },

        /**
         *
         */
        async checkGoogleDriveAccount() {
            if (this.project && this.googleDrive === undefined) {
                const { data } = await GoogleDriveService.get(
                    this.project.slug
                );
                this.googleDrive = data;
            }
        },

        /**
         *
         */
        async checkGoogleAuthenticator() {
            if (this.googleAuthenticator === undefined) {
                const { data } = await GoogleAuthenticatorService.check();
                this.googleAuthenticator = data ? true : false;
            }
        },

        /**
         *
         */
        addGoogleDriveAccount() {
            window.location.href =
                "/google/drive/auth?project=" + this.project.slug;
        },

        /**
         *
         */
        async removeGoogleDriveAccount() {
            this.removingGoogleDrive = true;
            const { data } = await GoogleDriveService.destroy(
                this.project.slug,
                this.googleDrive.id
            );
            this.removingGoogleDrive = false;
            this.googleDrive = undefined;
        },

        /**
         *
         */
        addGoogleCalendarAccount() {
            window.location.href = "/google/calendar/auth";
        },

        /**
         *
         */
        async removeGoogleCalendarAccount() {
            this.removingGoogleCalendar = true;
            const { data } = await GoogleCalendarService.destroy(
                this.googleCalendar.id
            );
            this.removingGoogleCalendar = false;
            this.googleCalendar = undefined;
        },

        /**
         *
         */
        addGoogleContactAccount() {
            window.location.href = "/google/contact/auth";
        },

        /**
         *
         */
        async removeGoogleContactAccount() {
            this.removingGoogleContact = true;
            const { data } = await GoogleContactService.destroy(
                this.googleContact.id
            );
            this.removingGoogleContact = false;
            this.googleContact = undefined;
        },

        /**
         *
         */
        addGoogleAuthenticator() {
            this.$router.push({
                name: "two-factors.enable",
            });
        },

        /**
         *
         */
        async removeGoogleAuthenticator() {
            this.removingGoogleAuthenticator = true;
            const { data } = await GoogleAuthenticatorService.deactivate();
            this.removingGoogleAuthenticator = false;
            this.googleAuthenticator = false;
        },

        /**
         *
         */
        toggleGoogleDriveAccount() {
            if (this.googleDrive) {
                this.removeGoogleDriveAccount();
            } else {
                this.addGoogleDriveAccount();
            }
        },

        /**
         *
         */
        toggleGoogleCalendarAccount() {
            if (this.googleCalendar) {
                this.removeGoogleCalendarAccount();
            } else {
                this.addGoogleCalendarAccount();
            }
        },

        /**
         *
         */
        toggleGoogleContactAccount() {
            if (this.googleContact) {
                this.removeGoogleContactAccount();
            } else {
                this.addGoogleContactAccount();
            }
        },

        /**
         *
         */
        toggleGoogleAuthenticator() {
            if (this.googleAuthenticator === undefined) {
                return;
            }

            if (this.googleAuthenticator) {
                hcConfirm(
                    this.$t("setting.google_auth.confirm.disable"),
                    this.removeGoogleAuthenticator
                );
            } else {
                hcConfirm(
                    this.$t("setting.google_auth.confirm.enable"),
                    this.addGoogleAuthenticator
                );
            }
        },

        /**
         *
         */
        managePipedriveApi() {
            store.commit(OPEN_MODAL, "setting-pipedrive");
        },

        manageLocale() {
            store.commit(OPEN_SUB_SLIDE, "locales");
        },

        manageTutorials() {
            store.commit(OPEN_SUB_SLIDE, "tutorials");
        },

        managePappers() {
            store.commit(OPEN_SUB_SLIDE, "api-pappers");
        },
    },

    computed: {
        ...mapGetters("auth", ["user", "userFullName", "impersonating"]),
        ...mapGetters(["project", "projectUserSettingsEventsReminder", "can"]),

        /**
         *
         */
        gDriveURL() {
            return this.googleDrive
                ? "/google/drive/auth"
                : "/google/drive/auth";
        },

        /**
         *
         */
        gCalendarURL() {
            return this.googleCalendar
                ? "/google/calendar/auth"
                : "/google/calendar/auth";
        },

        /**
         *
         */
        gContactURL() {
            return this.googleContact
                ? "/google/contact/auth"
                : "/google/contact/auth";
        },

        locale() {
            return locales.find((l) => l.locale == this.$i18n.locale);
        },

        remindEvent: {
            get() {
                return this.projectUserSettingsEventsReminder;
            },
            set(value) {
                store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "events.reminder",
                    value: value,
                    user: this.user.id,
                });
            },
        },
    },
};
</script>
