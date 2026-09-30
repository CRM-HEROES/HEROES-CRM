<template>
    <slide
        :name="name"
        @open="fetchInteractions(), fetchSelectedProspects()"
        :title="
            $t('prospect.interaction.title', {
                prospect: interactionProspect
                    ? interactionProspect.last_name
                    : '',
            })
        "
        :url="
            interactionProspect
                ? {
                      name: 'prospect.show',
                      params: {
                          project: project.slug,
                          prospect: interactionProspect.id,
                      },
                  }
                : null
        "
        :left="true"
        icon="fa fa-phone"
        :style="{
            width:
                tab == 1 && frameTab == 0
                    ? '395px'
                    : tab == 1 && frameTab == 1
                    ? '300px'
                    : '300px',
        }"
    >
        <tab-layout :count="2" :tab="tab" class="hc-flex-1">
            <template #1 v-if="interactionProspect">
                <item-list style="height: 100%; overflow: auto" padding="12px">
                    <template v-if="can('all.prospect.interaction.add')">
                        <item
                            v-if="interactionProspect.phone_number"
                            @click.prevent="(tab = 1), (frameTab = 3)"
                        >
                            <icon class="fa fa-phone" />
                            <div
                                class="hc-item-main-content"
                                v-text="
                                    $t('prospect.interaction.edit_phone_number')
                                "
                            ></div>
                            <icon class="fa fa-caret-right" />
                        </item>
                        <item
                            v-if="interactionProspect.mobile_phone_number"
                            @click.prevent="(tab = 1), (frameTab = 4)"
                        >
                            <icon class="fa fa-mobile" />
                            <div
                                class="hc-item-main-content"
                                v-text="
                                    $t(
                                        'prospect.interaction.edit_mobile_phone_number'
                                    )
                                "
                            ></div>
                            <icon class="fa fa-caret-right" />
                        </item>
                        <template
                            v-for="number in [
                                interactionProspect.phone_number,
                                interactionProspect.mobile_phone_number,
                            ].filter((n) => n)"
                            :key="number"
                        >
                            <!-- Telephone -->
                            <item
                                tag="a"
                                class="hc-prospect-interaction-item"
                                @click="interactionViaTelephone(number)"
                                :href="'tel:' + number"
                            >
                                <icon class="fa fa-phone" color="#489f1f" />
                                <div
                                    class="hc-item-main-content hc-flex-column"
                                >
                                    <span
                                        v-text="
                                            $t(
                                                'prospect.interaction.call_by_phone'
                                            )
                                        "
                                    ></span>
                                    <span
                                        class="hc-prospect-interaction-item-number"
                                        v-text="number"
                                    ></span>
                                </div>
                            </item>

                            <!-- Aircall -->
                            <item
                                class="hc-prospect-interaction-item"
                                @click="interactionViaAircall(number)"
                            >
                                <icon>
                                    <svg viewBox="0 0 40 40">
                                        <path
                                            fill="#00B388"
                                            d="M39.1,9.8c-0.9-4.5-4.5-8-9-9C27.9,0.3,24.2,0,20,0S12.1,0.3,9.9,0.8c-4.5,0.9-8,4.5-9,9 c-0.5,2.3-0.9,6-0.9,10.2c0,4.2,0.3,7.9,0.9,10.2c0.9,4.5,4.5,8,9,9C12.1,39.6,15.8,40,20,40s7.9-0.3,10.1-0.9c4.5-0.9,8-4.5,9-9 c0.5-2.3,0.9-6,0.9-10.2C39.9,15.8,39.6,12.1,39.1,9.8z M29.3,30.5C29.3,30.5,29.3,30.5,29.3,30.5c-0.7,0.3-1.9,0.5-3.5,0.6 c-0.1,0-0.1,0-0.2,0c-0.3,0-0.6-0.2-0.8-0.5c-0.4-0.9-1.2-1.6-2.2-1.8c-0.6-0.1-1.5-0.2-2.6-0.2s-2,0.1-2.6,0.2 c-1,0.2-1.8,0.9-2.2,1.8c-0.1,0.3-0.4,0.5-0.8,0.5c-0.1,0-0.2,0-0.2,0c-1.6-0.2-2.8-0.4-3.5-0.6c0,0,0,0,0,0 c-0.5-0.2-0.8-0.6-0.8-1.2c0,0,0,0,0,0c0,0,0,0,0-0.1c0,0,0,0,0,0c0,0,0,0,0,0c0.1-1.6,1.1-5.5,2.6-9.8c1.7-5,3.5-9,4.2-9.8 c0.1-0.1,0.3-0.2,0.4-0.3c0.1,0,0.1-0.1,0.2-0.1c0,0,0,0,0,0c0.5-0.2,1.5-0.3,2.6-0.3c1.1,0,2.1,0.1,2.6,0.3c0,0,0,0,0,0 c0.1,0,0.2,0.1,0.2,0.1c0.2,0.1,0.3,0.2,0.4,0.3c0,0,0,0,0,0c0.8,0.8,2.6,4.8,4.2,9.8c1.5,4.4,2.5,8.2,2.6,9.8c0,0,0,0,0,0 c0,0,0,0,0,0c0,0,0,0,0,0.1c0,0,0,0,0,0C30.1,29.8,29.8,30.3,29.3,30.5z"
                                        ></path>
                                    </svg>
                                </icon>
                                <div
                                    class="hc-item-main-content hc-flex-column"
                                >
                                    <span
                                        v-text="
                                            $t(
                                                'prospect.interaction.call_by_aircall'
                                            )
                                        "
                                    ></span>
                                    <span
                                        class="hc-prospect-interaction-item-number"
                                        v-text="number"
                                    ></span>
                                </div>
                                <icon class="fa fa-caret-right" />
                            </item>

                            <!-- Ringover -->
                            <item
                                class="hc-prospect-interaction-item"
                                @click="interactionViaRingover(number)"
                            >
                                <icon>
                                    <svg viewBox="0 0 40 40">
                                        <path
                                            d="M9.9,16.9c1.3-4.3,5.3-7.4,10.1-7.4s8.7,3.1,10.1,7.4h9.7C38.2,7.3,30,0,20,0S1.8,7.3,0.3,16.9H9.9z"
                                            style="fill: rgb(85, 195, 192)"
                                        ></path>
                                        <path
                                            d="M30.1,23.1c-1.3,4.3-5.3,7.4-10.1,7.4s-8.7-3.1-10.1-7.4H0.3C1.8,32.7,10,40,20,40s18.2-7.3,19.7-16.9H30.1z"
                                            style="fill: rgb(85, 195, 192)"
                                        ></path>
                                    </svg>
                                </icon>
                                <div
                                    class="hc-item-main-content hc-flex-column"
                                >
                                    <span
                                        v-text="
                                            $t(
                                                'prospect.interaction.call_by_ringover'
                                            )
                                        "
                                    ></span>
                                    <span
                                        class="hc-prospect-interaction-item-number"
                                        v-text="number"
                                    ></span>
                                </div>
                                <icon class="fa fa-caret-right" />
                            </item>
                        </template>

                        <!-- Add history -->
                        <item
                            tag="a"
                            class="hc-prospect-interaction-item"
                            @click="addHistory()"
                        >
                            <icon class="fa fa-plus icon-green" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('prospect.interaction.add_history')"
                            ></div>
                            <loading :loading="addingHistory" />
                        </item>
                    </template>
                    <item
                        style="
                            background-color: #7939b8;
                            color: white;
                            margin-top: 10px;
                            margin-bottom: 10px;
                        "
                        v-if="prospectInteractions.length > 0"
                    >
                        <icon class="fa fa-clock" color="white"></icon>
                        <div
                            class="hc-main-content"
                            v-text="$t('prospect.interaction.history')"
                        ></div>
                    </item>
                    <interaction-row
                        v-for="c in prospectInteractions"
                        :key="c.id"
                        :interaction="c"
                    />
                </item-list>
            </template>

            <!-- List of interaction -->
            <template #2>
                <frame-layout :count="5" :tab="frameTab" class="hc-flex-1">
                    <template #1 v-if="interactionProspect">
                        <tab-layout
                            :count="2"
                            :tab="aircallTab"
                            class="hc-flex-1"
                        >
                            <template #1>
                                <div
                                    class="hc-flex-column"
                                    style="height: 100%"
                                >
                                    <item @click="tab = 0" class="bordered">
                                        <icon class="fa fa-caret-left" />
                                        <div
                                            class="hc-item-main-content"
                                            v-text="
                                                $t(
                                                    'prospect.interaction.call_by_aircall'
                                                )
                                            "
                                        ></div>
                                        <icon
                                            class="fa fa-cog"
                                            @click.stop="aircallTab = 1"
                                        />
                                    </item>
                                    <div
                                        style="
                                            flex: 1;
                                            width: 100%;
                                            height: 100%;
                                            padding: 10px;
                                            overflow: auto;
                                        "
                                    >
                                        <aircall
                                            id="aircall-phone"
                                            :number="interaction.number"
                                            :style="{
                                                width: '100%',
                                                height: '100%',
                                                display:
                                                    tab == 1 && frameTab == 0
                                                        ? 'block'
                                                        : 'none',
                                            }"
                                            @outgoing-call="
                                                (callInfos) => {
                                                    interaction.status =
                                                        'initiated';
                                                    interaction.data = {
                                                        id: callInfos.call_id,
                                                    };
                                                    updateInteraction();
                                                }
                                            "
                                            @call-ended="
                                                (callInfos) => {
                                                    interaction.status =
                                                        'ended';
                                                    interaction.data = {
                                                        id: callInfos.call_id,
                                                    };
                                                    updateInteraction();
                                                    nextInteraction();
                                                }
                                            "
                                            @answered-call="
                                                (interaction.status =
                                                    'answered'),
                                                    updateInteraction()
                                            "
                                        />
                                    </div>
                                </div>
                            </template>
                            <template #2>
                                <div
                                    class="hc-flex-column"
                                    style="height: 100%"
                                >
                                    <item
                                        @click="aircallTab = 0"
                                        class="bordered"
                                    >
                                        <icon class="fa fa-caret-left" />
                                        <div
                                            class="hc-item-main-content"
                                            v-text="'Paramètre Aircall Webhook'"
                                        ></div>
                                    </item>
                                    <item-list
                                        class="hc-flex-1"
                                        gap="5px"
                                        style="overflow: auto"
                                    >
                                        <item
                                            tag="a"
                                            href="https://dashboard.aircall.io/integrations/flow/install/webhook/webhook/0"
                                            target="_blank"
                                        >
                                            <icon class="fa fa-wifi" />
                                            <div
                                                class="hc-item-main-content"
                                                v-text="
                                                    'Rendez-vous sur la page webhook d\'aircall'
                                                "
                                            ></div>
                                            <icon class="fa fa-caret-right" />
                                        </item>
                                        <item
                                            tag="a"
                                            @click.prevent="
                                                copyAircallWebhookURLToClipboard
                                            "
                                        >
                                            <icon class="fa fa-link" />
                                            <div
                                                class="hc-item-main-content"
                                                v-text="
                                                    'Mettre ' +
                                                    aircallWebhookURL +
                                                    ' comme URL'
                                                "
                                            ></div>
                                            <icon class="fa fa-copy" />
                                        </item>
                                        <item>
                                            <icon class="fa fa-check" />
                                            <div
                                                class="hc-item-main-content"
                                                v-text="
                                                    'Cocher &quot;call.ended&quot; dans la section Appel'
                                                "
                                            ></div>
                                        </item>
                                        <item>
                                            <icon class="fa fa-check" />
                                            <div
                                                class="hc-item-main-content"
                                                v-text="
                                                    'Enfin cliquer sur &quot;Ajouter webhook&quot;'
                                                "
                                            ></div>
                                        </item>
                                    </item-list>
                                </div>
                            </template>
                        </tab-layout>
                    </template>

                    <template #2 v-if="interactionProspect">
                        <div class="hc-flex-column" style="height: 100%">
                            <item @click="tab = 0" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t(
                                            'prospect.interaction.call_by_ringover'
                                        )
                                    "
                                ></div>
                                <icon
                                    class="fa fa-cog"
                                    @click.stop="ringoverSetting"
                                />
                            </item>
                            <div
                                style="
                                    flex: 1;
                                    width: 100%;
                                    height: 100%;
                                    overflow: auto;
                                "
                            >
                                <ringover
                                    id="ringover-phone"
                                    :number="interaction.number"
                                    tab="phone"
                                    style="flex: 1; width: 100%; height: 100%"
                                    @ringing-call="
                                        (callInfo) => {
                                            interaction.from_number =
                                                callInfo.data.from;
                                            interaction.status = 'ringing';
                                            interaction.data.id =
                                                callInfo.data.call_id;
                                            updateInteraction();
                                        }
                                    "
                                    @hangup-call="
                                        (callInfo) => {
                                            interaction.status = 'hangup';
                                            interaction.data.id =
                                                callInfo.data.call_id;
                                            updateInteraction();
                                            nextInteraction();
                                        }
                                    "
                                    @answered-call="
                                        (interaction.status = 'answered'),
                                            updateInteraction()
                                    "
                                />
                            </div>
                        </div>
                    </template>

                    <template #3>
                        <select-prospect
                            @back="tab = 0"
                            @prospect-selected="setInteractionProspect"
                        />
                    </template>

                    <template #4>
                        <form
                            class="hc-flex-column"
                            style="height: 100%"
                            @submit.prevent="updateProspectPhoneNumber"
                        >
                            <item @click="tab = 0" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t(
                                            'prospect.interaction.edit_phone_number'
                                        )
                                    "
                                ></div>
                            </item>
                            <item-list padding="12px" style="height: auto">
                                <v-field
                                    :label="$t('field.prospect.phone_number')"
                                    ><input
                                        type="tel"
                                        v-model.lazy="phoneNumber"
                                /></v-field>
                            </item-list>
                            <buttons>
                                <button v-text="$t('update')"></button>
                            </buttons>
                            <loading :loading="updatingPhoneNumber" />
                        </form>
                    </template>

                    <template #5>
                        <form
                            class="hc-flex-column"
                            style="height: 100%; position"
                            @submit.prevent="updateProspectMobilePhoneNumber"
                        >
                            <item @click="tab = 0" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t(
                                            'prospect.interaction.edit_mobile_phone_number'
                                        )
                                    "
                                ></div>
                            </item>
                            <item-list padding="12px" style="height: auto">
                                <v-field
                                    :label="$t('field.prospect.phone_number')"
                                    ><input
                                        type="tel"
                                        v-model.lazy="mobilePhoneNumber"
                                /></v-field>
                            </item-list>
                            <buttons>
                                <button v-text="$t('update')"></button>
                            </buttons>
                            <loading :loading="updatingMobilePhoneNumber" />
                        </form>
                    </template>
                </frame-layout>
            </template>
        </tab-layout>
    </slide>
</template>

<style>
.hc-prospect-interaction-item {
    padding: 4px 0 !important;
    text-decoration: none;
}
.hc-prospect-interaction-item-number {
    font-size: 11px;
    color: #999999;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import ProspectService from "@/apis/project/prospect";

import { OPEN_MODAL } from "@/actions/modal";
import { SET_PROSPECT, UPDATE_PROSPECT } from "@/actions/project/prospect";
import { SET_INTERACTION_PROSPECT } from "@/actions/project/prospect/interaction";
import {
    FETCH_PROSPECT_INTERACTIONS,
    ADD_PROSPECT_INTERACTION,
    UPDATE_PROSPECT_INTERACTION,
    SET_PROSPECT_INTERACTION_TAB,
    SET_PROSPECT_INTERACTION_FRAME_TAB,
} from "@/actions/project/prospect/interaction";

// Components
import Ringover from "@/components/utils/Ringover.vue";
import Aircall from "@/components/utils/Aircall.vue";
import InteractionRow from "./InteractionRow.vue";
import SelectProspect from "../select/Select.vue";

export default {
    components: {
        Ringover,
        Aircall,
        InteractionRow,
        SelectProspect,
    },

    data() {
        return {
            name: "prospect-manage-interactions",
            tab: 0,
            frameTab: 0,
            aircallTab: 0,
            currentProspectIndex: 0,
            selectedProspects: [],
            phoneNumber: null,
            mobilePhoneNumber: null,
            interaction: this.newInteraction(),
            addingHistory: false,
            fetchingInteraction: false,
            updatingPhoneNumber: false,
            updatingMobilePhoneNumber: false,
        };
    },

    created() {
        store.commit(SET_PROSPECT_INTERACTION_TAB, 0);
        store.commit(SET_PROSPECT_INTERACTION_FRAME_TAB, 0);
    },

    methods: {
        newInteraction() {
            return {
                source: "telephone",
                number: "",
                status: "new",
                data: {},
            };
        },

        async fetchInteractions() {
            if (this.interactionProspect) {
                this.phoneNumber = this.interactionProspect.phone_number;
                this.mobilePhoneNumber =
                    this.interactionProspect.mobile_phone_number;

                this.fetchingInteraction = true;

                try {
                    await store.dispatch(FETCH_PROSPECT_INTERACTIONS);
                } finally {
                    this.fetchingInteraction = false;
                }
            } else if (this.prospectsSelected.length == 0) {
                this.tab = 1;
                this.frameTab = 2;
            }
        },

        addHistory() {
            this.addingHistory = true;
            this.interaction = this.newInteraction();
            this.interaction.source = "telephone";
            this.interaction.number = "";
            this.interaction.from_number = this.user.mobile_phone_number
                ? this.user.mobile_phone_number
                : this.user.phone_number
                ? this.user.phone_number
                : "";
            this.addInteraction().finally(() => {
                this.addingHistory = false;
            });
        },

        interactionViaTelephone(number) {
            this.interaction = this.newInteraction();
            this.interaction.source = "telephone";
            this.interaction.number = number;
            this.addInteraction();
        },

        interactionViaAircall(number) {
            this.tab = 1;
            this.frameTab = 0;
            this.interaction = this.newInteraction();
            this.interaction.source = "aircall";
            this.interaction.number = number;
            this.addInteraction();
        },

        interactionViaRingover(number) {
            this.tab = 1;
            this.frameTab = 1;
            this.interaction = this.newInteraction();
            this.interaction.source = "ringover";
            this.interaction.number = number;
            this.addInteraction();
        },

        /**
         *
         */
        async addInteraction() {
            this.addingInteraction = true;

            try {
                this.interaction = await store.dispatch(
                    ADD_PROSPECT_INTERACTION,
                    this.interaction
                );
            } finally {
                this.addingInteraction = false;
            }
        },

        /**
         *
         */
        async updateInteraction() {
            if (!this.interaction) {
                return;
            }

            if (!this.interaction.id) {
                this.interaction = await store.dispatch(
                    ADD_PROSPECT_INTERACTION,
                    this.interaction
                );
                return;
            }

            store.dispatch(UPDATE_PROSPECT_INTERACTION, this.interaction);
        },

        /**
         *
         */
        nextInteraction() {
            if (
                this.selectedProspects.length - 1 >
                this.this.currentProspectIndex
            ) {
                this.currentProspectIndex++;
            } else {
                store.commit(SET_INTERACTION_PROSPECT, null);
            }
        },

        /**
         *
         */
        setInteractionProspect(prospect) {
            store.commit(SET_PROSPECT, prospect);
            this.tab = 0;
        },

        async updateProspectPhoneNumber() {
            this.updatingPhoneNumber = true;

            try {
                await store.dispatch(UPDATE_PROSPECT, {
                    id: this.interactionProspect.id,
                    phone_number: this.phoneNumber,
                });
            } finally {
                this.updatingPhoneNumber = false;
                this.tab = 0;
            }
        },

        async updateProspectMobilePhoneNumber() {
            this.updatingMobilePhoneNumber = true;

            try {
                await store.dispatch(UPDATE_PROSPECT, {
                    id: this.interactionProspect.id,
                    mobile_phone_number: this.mobilePhoneNumber,
                });
            } finally {
                this.updatingMobilePhoneNumber = false;
                this.tab = 0;
            }
        },

        async fetchSelectedProspects() {
            if (this.prospectsSelected.length == 0) {
                this.selectedProspects = [];
                return;
            }

            try {
                const { data } = await ProspectService.get(this.project.slug, {
                    params: {
                        filters: JSON.stringify({
                            ids: this.prospectsSelected,
                        }),
                        fields: "first_name,last_name,phone_number,mobile_phone_number",
                    },
                });
                this.selectedProspects = data.data.filter(
                    (prospect) =>
                        prospect.phone_number || prospect.mobile_phone_number
                );
            } finally {
                this.fetchingProspect = false;
            }
        },

        /**
         * Copy webhook URL to clipboard
         */
        copyAircallWebhookURLToClipboard() {
            navigator.clipboard.writeText(this.aircallWebhookURL);

            flashInfo({
                title: "Aircall",
                body: "URL Webhook Aircall copié",
                duration: 5000,
            });
        },

        ringoverSetting() {
            store.commit(OPEN_MODAL, "setting-ringover");
        },
    },

    watch: {
        async interactionProspect(newValue, oldValue) {
            if (newValue && this.leftSlideOpen(this.name)) {
                this.fetchInteractions();

                if (
                    newValue.phone_number &&
                    (!oldValue ||
                        oldValue.phone_number == this.interaction.number)
                ) {
                    this.interaction.number = newValue.phone_number;
                } else {
                    this.interaction.number = newValue.mobile_phone_number;
                }
            }
        },

        selectedProspects() {
            this.currentProspectIndex = 0;
        },

        interactionTab() {
            this.tab = this.interactionTab;
        },

        interactionFrameTab() {
            this.frameTab = this.interactionFrameTab;
        },

        currentProspect(newValue) {
            if (newValue) {
                setTimeout(() => {
                    store.commit(SET_INTERACTION_PROSPECT, newValue);
                }, 1000);
            }
        },
    },

    computed: {
        ...mapGetters("auth", ["user"]),
        ...mapGetters([
            "project",
            "interactionProspect",
            "prospectFullName",
            "prospectInteractions",
            "interactionTab",
            "interactionFrameTab",
            "prospectsSelected",
            "leftSlideOpen",
            "can",
        ]),

        currentProspect() {
            if (this.selectedProspects.length > this.currentProspectIndex) {
                return this.selectedProspects[this.currentProspectIndex];
            }

            return null;
        },

        /**
         * Webhook URL
         * for web service import
         */
        aircallWebhookURL: function () {
            return window.location.origin + "/webhook/aircall";
        },
    },
};
</script>
