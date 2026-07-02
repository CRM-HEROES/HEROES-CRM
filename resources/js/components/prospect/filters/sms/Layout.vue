<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <item-list padding="12px" style="height: 100%; overflow: auto">
                <item tag="label">
                    <icon class="fa fa-filter" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('prospect.table.filters.sms.with_sms')"
                    ></div>
                    <icon
                        class="fa fa-thumbs-down"
                        :style="{ color: exclude ? '#DD0000' : '#CCCCCC' }"
                        @click.prevent.stop="exclude = !exclude"
                    />
                    <checkbox
                        v-model="smsParamExists"
                        style="margin-right: 5px"
                    />
                </item>
                <item
                    class="hc-prospect-filter-event-item"
                    @click="(tab = 1), (frameTab = 0)"
                >
                    <icon class="fa fa-paper-plane" />
                    <div class="hc-item-main-content hc-flex-column">
                        <span
                            class="hc-prospect-filter-event-item-title"
                            v-text="$t('prospect.table.filters.sms.sources')"
                        ></span>
                        <span
                            class="hc-prospect-filter-event-item-value"
                            v-text="
                                filterSourcesName
                                    ? filterSourcesName
                                    : 'Aucune source sélectionnée ...'
                            "
                        ></span>
                    </div>
                    <icon class="fa fa-caret-right" />
                </item>
                <item
                    class="hc-prospect-filter-event-item"
                    @click="(tab = 1), (frameTab = 1)"
                >
                    <icon class="fa fa-user" />
                    <div class="hc-item-main-content hc-flex-column">
                        <span
                            class="hc-prospect-filter-event-item-title"
                            v-text="$t('prospect.table.filters.sms.created_by')"
                        ></span>
                        <span
                            class="hc-prospect-filter-event-item-value"
                            v-text="
                                filterCreatorsName
                                    ? filterCreatorsName
                                    : $t('none') + ' ...'
                            "
                        ></span>
                    </div>
                    <icon class="fa fa-caret-right" />
                </item>
                <item tag="label">
                    <icon class="fa fa-clock" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('prospect.table.filters.sms.after')"
                    ></div>
                    <input
                        type="date"
                        v-model="filterCreatedAfter"
                        class="hc-prospect-filter-event-item-date"
                    />
                    <icon
                        v-if="filterCreatedAfter"
                        tag="a"
                        class="fa fa-times"
                        @click.prevent.stop="filterCreatedAfter = null"
                    />
                    <icon v-else class="fa fa-plus" />
                </item>
                <item tag="label">
                    <icon class="fa fa-clock" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('prospect.table.filters.sms.before')"
                    ></div>
                    <input
                        type="date"
                        v-model="filterCreatedBefore"
                        class="hc-prospect-filter-event-item-date"
                    />
                    <icon
                        v-if="filterCreatedBefore"
                        tag="a"
                        class="fa fa-times"
                        @click.prevent.stop="filterCreatedBefore = null"
                    />
                    <icon v-else class="fa fa-plus" />
                </item>
                <item tag="label">
                    <icon class="fa fa-envelope-circle-check" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('prospect.table.filters.sms.sent')"
                    ></div>
                    <checkbox v-model="filterSent" style="margin-right: 5px" />
                </item>
                <item tag="label">
                    <icon class="fa fa-thumbs-down" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('prospect.table.filters.sms.not_sent')"
                    ></div>
                    <checkbox
                        v-model="filterNotSent"
                        style="margin-right: 5px"
                    />
                </item>
                <item tag="label">
                    <icon class="fa fa-triangle-exclamation" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('prospect.table.filters.sms.with_error')"
                    ></div>
                    <checkbox
                        v-model="filterWithError"
                        style="margin-right: 5px"
                    />
                </item>
                <item tag="label">
                    <icon class="fa fa-circle-check" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('prospect.table.filters.sms.without_error')"
                    ></div>
                    <checkbox
                        v-model="filterWithoutError"
                        style="margin-right: 5px"
                    />
                </item>
            </item-list>
        </template>

        <template #2>
            <frame-layout :count="3" :tab="frameTab" class="hc-flex-1">
                <template #1>
                    <div
                        class="hc-flex-column"
                        style="height: 100%; position: relative"
                    >
                        <item @click="tab = 0" class="bordered">
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content hc-flex-column"
                                v-text="
                                    $t(
                                        'prospect.table.filters.sms.select_sources'
                                    )
                                "
                            ></div>
                        </item>
                        <item-list
                            class="hc-flex-1"
                            padding="12px"
                            style="position: relative"
                        >
                            <source-row
                                v-for="source in sources"
                                :key="source.key"
                                :source="source"
                                v-model="filterSources"
                            />
                        </item-list>
                    </div>
                </template>

                <template #2>
                    <div
                        class="hc-flex-column"
                        style="height: 100%; position: relative"
                    >
                        <item
                            @click="tab = 0"
                            style="border-bottom: 1px solid #eee"
                        >
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content hc-flex-column"
                                v-text="
                                    $t('prospect.table.filters.sms.created_by')
                                "
                            ></div>
                        </item>
                        <search v-model="userKeyword" />
                        <item-list
                            class="hc-flex-1"
                            padding="12px"
                            style="position: relative"
                        >
                            <user-row
                                v-for="user in filteredUsers"
                                :key="user.id"
                                :user="user"
                                v-model="filterCreators"
                            />
                            <loading :loading="fetchingUsers" />
                        </item-list>
                    </div>
                </template>
            </frame-layout>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    REMOVE_PROSPECT_PARAMS,
    ADD_PROSPECT_PARAMS,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";

// Components
import SourceRow from "./SourceRow.vue";
import UserRow from "./UserRow.vue";

export default {
    components: {
        SourceRow,
        UserRow,
    },

    data() {
        return {
            tab: 0,
            frameTab: 0,
            userKeyword: "",
            sources: [
                {
                    key: "telephone",
                    name: "Téléphone",
                },
                {
                    key: "ringover",
                    name: "Ringover",
                },
                {
                    key: "smsbox",
                    name: "SMSBOX",
                },
                {
                    key: "whatsapp",
                    name: "Whatsapp",
                },
                {
                    key: "ultramsg",
                    name: "Ultramsg",
                },
                {
                    key: "mtarget",
                    name: "MTarget",
                },
            ],
            exclude: false,
            fetchingUsers: false,
        };
    },

    methods: {
        changeSms(event) {
            // Remove without param
            store.commit(REMOVE_PROSPECT_PARAMS, {
                key: this.withoutKeySms,
            });

            // Add or remove with param
            store.commit(
                event.target.checked
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKeySms,
                    value: [],
                }
            );
            store.dispatch(FETCH_PROSPECTS);
        },
    },

    computed: {
        ...mapGetters(["prospectsParamExists", "prospectsParamValue", "users"]),

        /**
         *
         */
        withKeySms() {
            return "withSms";
        },

        /**
         *
         */
        withoutKeySms() {
            return "withoutSms";
        },

        /**
         *
         */
        filterKey() {
            return this.exclude ? this.withoutKeySms : this.withKeySms;
        },

        /**
         *
         */
        smsParamExists: {
            get() {
                return this.prospectsParamExists(this.filterKey);
            },
            set(value) {
                this.smsParams = value ? {} : null;
            },
        },

        /**
         * Extract sms params
         * from prospects query params
         *
         * Sms params format
         * "products:id1,id2,...;users:id1,id2,...;sent:1;error:0;...""
         */
        smsParams: {
            get() {
                // Check if sms params exists
                if (!this.prospectsParamExists(this.filterKey)) {
                    return null;
                }

                // Extract sms params
                // from string to object
                // {
                //    sources: "source1,source2,...",
                // }
                return this.prospectsParamValue(this.filterKey);
            },
            set(value) {
                // Remove sms params to
                // prospect params
                store.commit(REMOVE_PROSPECT_PARAMS, {
                    key: this.withKeySms,
                });
                store.commit(REMOVE_PROSPECT_PARAMS, {
                    key: this.withoutKeySms,
                });

                if (value !== null) {
                    // Add sms params to
                    // prospect params
                    store.commit(ADD_PROSPECT_PARAMS, {
                        key: this.filterKey,
                        value: value,
                    });
                }

                store.dispatch(FETCH_PROSPECTS);
            },
        },

        /**
         *
         */
        filterSources: {
            get() {
                const smsParams = this.smsParams;

                if (!smsParams || !smsParams.withSources) {
                    return [];
                }

                return smsParams.withSources;
            },
            set(value) {
                const smsParams = this.smsParams || {};
                if (value.length > 0) {
                    smsParams.withSources = value;
                } else if (smsParams.withSources) {
                    delete smsParams.withSources;
                }
                this.smsParams =
                    Object.keys(smsParams).length == 0 ? null : smsParams;
            },
        },

        /**
         *
         */
        filterCreators: {
            get() {
                const smsParams = this.smsParams;

                if (
                    !smsParams ||
                    !smsParams.withCreators ||
                    !smsParams.withCreators.ids
                ) {
                    return [];
                }

                return smsParams.withCreators.ids;
            },
            set(value) {
                const smsParams = this.smsParams || {};
                if (value.length > 0) {
                    if (!smsParams.withCreators) {
                        smsParams.withCreators = {};
                    }

                    smsParams.withCreators.ids = value;
                } else if (
                    smsParams.withCreators &&
                    smsParams.withCreators.ids
                ) {
                    delete smsParams.withCreators.ids;

                    if (Object.keys(smsParams.withCreators).length == 0) {
                        delete smsParams.withCreators;
                    }
                }
                this.smsParams =
                    Object.keys(smsParams).length == 0 ? null : smsParams;
            },
        },

        /**
         *
         */
        filterCreatedAfter: {
            get() {
                const smsParams = this.smsParams;

                if (!smsParams || !smsParams.createdAfter) {
                    return null;
                }

                return smsParams.createdAfter;
            },
            set(value) {
                const smsParams = this.smsParams || {};
                if (value) {
                    smsParams.createdAfter = value;
                } else if (smsParams.createdAfter) {
                    delete smsParams.createdAfter;
                }
                this.smsParams =
                    Object.keys(smsParams).length > 0 ? smsParams : null;
            },
        },

        /**
         *
         */
        filterCreatedBefore: {
            get() {
                const smsParams = this.smsParams;

                if (!smsParams || !smsParams.createdBefore) {
                    return null;
                }

                return smsParams.createdBefore;
            },
            set(value) {
                const smsParams = this.smsParams || {};
                if (value) {
                    smsParams.createdBefore = value;
                } else if (smsParams.createdBefore) {
                    delete smsParams.createdBefore;
                }
                this.smsParams =
                    Object.keys(smsParams).length > 0 ? smsParams : null;
            },
        },

        /**
         *
         */
        filterSent: {
            get() {
                const smsParams = this.smsParams;

                return (
                    smsParams &&
                    smsParams.sent !== undefined &&
                    smsParams.sent == 1
                );
            },
            set(value) {
                const smsParams = this.smsParams || {};
                if (value) {
                    smsParams.sent = 1;
                } else if (smsParams.sent !== undefined) {
                    delete smsParams.sent;
                }
                this.smsParams =
                    Object.keys(smsParams).length > 0 ? smsParams : null;
            },
        },

        /**
         *
         */
        filterNotSent: {
            get() {
                const smsParams = this.smsParams;

                return (
                    smsParams &&
                    smsParams.sent !== undefined &&
                    smsParams.sent == 0
                );
            },
            set(value) {
                const smsParams = this.smsParams || {};
                if (value) {
                    smsParams.sent = 0;
                } else if (smsParams.sent !== undefined) {
                    delete smsParams.sent;
                }
                this.smsParams =
                    Object.keys(smsParams).length > 0 ? smsParams : null;
            },
        },

        /**
         *
         */
        filterWithError: {
            get() {
                const smsParams = this.smsParams;

                return (
                    smsParams &&
                    smsParams.error !== undefined &&
                    smsParams.error == 1
                );
            },
            set(value) {
                const smsParams = this.smsParams || {};
                if (value) {
                    smsParams.error = 1;
                } else if (smsParams.error !== undefined) {
                    delete smsParams.error;
                }
                this.smsParams =
                    Object.keys(smsParams).length > 0 ? smsParams : null;
            },
        },

        /**
         *
         */
        filterWithoutError: {
            get() {
                const smsParams = this.smsParams;

                return (
                    smsParams &&
                    smsParams.error !== undefined &&
                    smsParams.error == 0
                );
            },
            set(value) {
                const smsParams = this.smsParams || {};
                if (value) {
                    smsParams.error = 0;
                } else if (smsParams.error !== undefined) {
                    delete smsParams.error;
                }
                this.smsParams =
                    Object.keys(smsParams).length > 0 ? smsParams : null;
            },
        },

        /**
         *
         */
        filterSourcesName() {
            return this.filterSources
                .map((fs) => this.sources.find((u) => u.id == fu))
                .filter((u) => u)
                .map((u) => u.name)
                .join(", ");
        },

        /**
         *
         */
        filterCreatorsName() {
            return this.filterCreators
                .map((fu) => this.users.find((u) => u.id == fu))
                .filter((u) => u)
                .map((u) => u.name)
                .join(", ");
        },

        /**
         *
         */
        filteredUsers() {
            const keyword = removeStringAccent(this.userKeyword);

            return this.users
                .filter(
                    (user) =>
                        removeStringAccent(user.name).indexOf(keyword) >= 0
                )
                .sort((user1, user2) =>
                    user1.pivot &&
                    user2.pivot &&
                    user1.pivot.relevance_prospect >
                        user2.pivot.relevance_prospect
                        ? -1
                        : 1
                );
        },
    },

    watch: {
        async tab(newValue) {
            if (newValue == 1) {
                // Calendars
                if (this.frameTab == 1) {
                    this.fetchingUsers = true;

                    try {
                        // await store.dispatch(FETCH_USERS);
                    } finally {
                        this.fetchingUsers = false;
                    }
                }
            }
        },

        exclude(value) {
            if (
                this.prospectsParamExists(
                    value ? this.withKeySms : this.withoutKeySms
                )
            ) {
                const prospectsParamValue = this.prospectsParamValue(
                    value ? this.withKeySms : this.withoutKeySms
                );

                this.smsParams = prospectsParamValue;
            }
        },
    },
};
</script>
