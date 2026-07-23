<template>
    <slide
        :name="name"
        @open="fetchSms(), fetchSelectedProspects()"
        :title="
            prospectsSelected.length > 0
                ? $t('prospect.sms.bulk_title', {
                      count: prospectsSelected.length,
                  })
                : $t('prospect.sms.title', { prospect: prospectFullName })
        "
        :url="
            prospect
                ? {
                      name: 'prospect.show',
                      params: {
                          project: project.slug,
                          prospect: prospect.id,
                      },
                  }
                : null
        "
        icon="fa fa-comments"
        :style="{ width: tab == 1 && frameTab == 1 ? '380px' : '340px' }"
    >
        <tab-layout :count="3" :tab="tab" class="hc-flex-1">
            <template #1>
                <item-list style="height: 100%; overflow: auto" padding="12px">
                    <!-- Telephone -->
                    <item
                        v-if="can('all.prospect.sms.add') && prospect"
                        tag="a"
                        class="hc-prospect-sms-item"
                        @click="sendSMSViaTelephone"
                        :href="'sms:' + prospect.mobile_phone_number"
                    >
                        <icon class="fa fa-phone" color="#489f1f" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                v-text="$t('prospect.sms.via_your_phone')"
                            ></span>
                            <span
                                class="hc-prospect-sms-item-number"
                                v-text="prospect.mobile_phone_number"
                            ></span>
                        </div>
                    </item>

                    <!-- WhatsApp -->
                    <item
                        v-if="can('all.prospect.sms.add') && prospect"
                        tag="a"
                        class="hc-prospect-sms-item"
                        :href="whatsappUrl"
                    >
                        <icon
                            ><svg viewBox="0 0 50 50">
                                <path
                                    id="XMLID_469_"
                                    style="fill: #31b04a"
                                    d="M36.9,28.9c-0.1,0-3.7-1.8-4.4-2.1c-0.3-0.1-0.5-0.2-0.8-0.2c-0.5,0-0.9,0.2-1.2,0.7
		c-0.4,0.5-1.5,1.8-1.8,2.2c0,0.1-0.1,0.1-0.1,0.1c0,0-0.6-0.2-0.8-0.3c-3.9-1.7-6.8-5.7-7.2-6.4c-0.1-0.1-0.1-0.1-0.1-0.1
		c0-0.1,0.1-0.2,0.2-0.3c0.2-0.2,0.4-0.5,0.6-0.7c0.1-0.1,0.2-0.2,0.3-0.3c0.3-0.3,0.4-0.6,0.6-0.9l0.1-0.2c0.4-0.8,0.1-1.4,0-1.6
		c-0.1-0.2-1.6-3.9-1.8-4.2c-0.4-0.9-0.9-1.4-1.6-1.4c-0.1,0,0,0-0.3,0c-0.3,0-2.2,0.3-3,0.8c-0.9,0.5-2.3,2.3-2.3,5.4
		c0,2.8,1.8,5.4,2.5,6.4c0,0,0.1,0.1,0.1,0.1c2.9,4.2,6.5,7.3,10.1,8.8c3.5,1.4,5.2,1.6,6.1,1.6c0,0,0,0,0,0c0.4,0,0.7,0,1-0.1
		l0.2,0c1.2-0.1,3.9-1.5,4.5-3.2c0.5-1.3,0.6-2.8,0.3-3.3C37.8,29.3,37.4,29.1,36.9,28.9z"
                                />
                                <path
                                    id="XMLID_470_"
                                    style="fill: #31b04a"
                                    d="M25.4,0.4C12,0.4,1.1,11.2,1.1,24.6c0,4.3,1.2,8.5,3.3,12.2L0.2,49.1c-0.1,0.2,0,0.5,0.2,0.7
		C0.5,49.9,0.7,50,0.8,50C0.9,50,1,50,1,50l12.9-4.1c3.5,1.9,7.5,2.9,11.5,2.9c13.4,0,24.4-10.8,24.4-24.2
		C49.8,11.2,38.9,0.4,25.4,0.4z M25.4,43.7c-3.8,0-7.5-1.1-10.6-3.2c-0.1-0.1-0.2-0.1-0.4-0.1c-0.1,0-0.1,0-0.2,0l-6.4,2.1l2.1-6.1
		c0.1-0.2,0-0.4-0.1-0.6c-2.4-3.3-3.7-7.2-3.7-11.2C6.1,14,14.8,5.4,25.4,5.4c10.6,0,19.3,8.6,19.3,19.1
		C44.7,35.1,36.1,43.7,25.4,43.7z"
                                />
                            </svg>
                        </icon>
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                v-text="$t('prospect.sms.via_whatsapp')"
                            ></span>
                            <span
                                class="hc-prospect-sms-item-number"
                                v-text="prospect.mobile_phone_number"
                            ></span>
                        </div>
                    </item>

                    <!-- SMSBOX -->
                    <item
                        class="hc-prospect-sms-item"
                        @click="
                            (tab = 1), (frameTab = 0), (sms.source = 'smsbox')
                        "
                    >
                        <icon>
                            <svg viewBox="0 0 50 40">
                                <path
                                    style="fill: #7d3a96"
                                    d="M42.8,40H7.2c-4,0-7.2-3.2-7.2-7.2V7c0-4,3.2-7.2,7.2-7.2h35.7C46.8-0.2,50,3,50,7v25.8 C50,36.8,46.8,40,42.8,40z"
                                />
                                <path
                                    style="fill: #ffffff"
                                    d="M26.6,34.3h-3.2c-4,0-7.3-3.3-7.3-7.3V13.6c0-4,3.3-7.3,7.3-7.3h3.2c4,0,7.3,3.3,7.3,7.3V27 C33.8,31,30.6,34.3,26.6,34.3z"
                                />
                                <path
                                    style="fill: #7e3b96"
                                    d="M27.4,23.4h-4.7c-2.2,0-3.9-1.8-3.9-3.9v-6.4c0-2.2,1.8-3.9,3.9-3.9h4.7c2.2,0,3.9,1.8,3.9,3.9v6.4 C31.3,21.7,29.5,23.4,27.4,23.4z"
                                />
                                <path
                                    style="fill: #ffffff"
                                    d="M31.9,11.8L31.9,11.8c-0.8,0-1.4-0.6-1.4-1.4V5.3c0-0.8,0.6-1.4,1.4-1.4h0c0.8,0,1.4,0.6,1.4,1.4v5.2 C33.3,11.2,32.7,11.8,31.9,11.8z"
                                />
                            </svg>
                        </icon>
                        <div class="hc-item-main-content hc-flex-column">
                            <span v-text="$t('prospect.sms.via_smsbox')"></span>
                            <span
                                v-if="prospect"
                                class="hc-prospect-sms-item-number"
                                v-text="prospect.mobile_phone_number"
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>

                    <!-- Ringover -->
                    <item
                        v-if="can('all.prospect.sms.add') && prospect"
                        class="hc-prospect-sms-item"
                        @click="
                            (tab = 1), (frameTab = 1), (sms.source = 'ringover')
                        "
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
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                v-text="$t('prospect.sms.via_ringover')"
                            ></span>
                            <span
                                class="hc-prospect-sms-item-number"
                                v-text="prospect.mobile_phone_number"
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>

                    <!-- UltraMsg -->
                    <item
                        v-if="can('all.prospect.sms.add')"
                        class="hc-prospect-sms-item"
                        @click="
                            (tab = 1), (frameTab = 0), (sms.source = 'ultramsg')
                        "
                    >
                        <icon>
                            <svg viewBox="0 0 50 50">
                                <path
                                    style="fill: #07b49b"
                                    d="M0.8,28.2l2.6-2.6C3.8,25.2,4.4,25,4.9,25h10v19.8l-2.6,2.6c-0.4,0.4-0.9,0.6-1.5,0.6h-10V28.2z"
                                />
                                <path
                                    style="fill: #07b49b"
                                    d="M18,17.4l2.6-2.6c0.4-0.4,0.9-0.6,1.5-0.6h10v30.7l-2.6,2.6C29,47.8,28.5,48,27.9,48H18V17.4z"
                                />
                                <path
                                    style="fill: #07b49b"
                                    d="M35.1,6l2.6-2.6c0.4-0.4,0.9-0.6,1.5-0.6h10v42l-2.6,2.6c-0.4,0.4-0.9,0.6-1.5,0.6h-10V6z"
                                />
                            </svg>
                        </icon>
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                v-text="$t('prospect.sms.via_ultramsg')"
                            ></span>
                            <span
                                v-if="prospect"
                                class="hc-prospect-sms-item-number"
                                v-text="prospect.mobile_phone_number"
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>

                    <!-- MTarget -->
                    <item
                        class="hc-prospect-sms-item"
                        @click="
                            (tab = 1), (frameTab = 0), (sms.source = 'mtarget')
                        "
                    >
                        <icon>
                            <svg viewBox="0 0 50 50">
                                <path
                                    style="fill: #11b37d"
                                    d="M9.4,47c0-11.9,0-23.9,0.1-35.8c2.9,11.9,5.8,23.9,8.7,35.8h14.3c2.9-11.9,5.8-23.8,8.7-35.7 c0.1,11.9,0.1,23.8,0.1,35.7H50c0-14.8,0-29.7,0-44.5c-5,0-10.1,0-15.1,0.1c-3.2,13-6.3,26-9.5,39c-3.3-13-6.5-26-9.6-39.1 c-5,0-10,0-15.1,0c0,14.8,0,29.7,0,44.5H9.4z"
                                />
                            </svg>
                        </icon>
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                v-text="$t('prospect.sms.via_mtarget')"
                            ></span>
                            <span
                                v-if="prospect"
                                class="hc-prospect-sms-item-number"
                                v-text="prospect.mobile_phone_number"
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>

                    <!-- Brevo -->
                    <item
                        v-if="can('all.prospect.sms.add')"
                        class="hc-prospect-sms-item"
                        @click="
                            (tab = 1), (frameTab = 0), (sms.source = 'brevo')
                        "
                    >
                        <icon>
                            <svg viewBox="0 0 50 50">
                                <rect
                                    x="2"
                                    y="2"
                                    width="46"
                                    height="46"
                                    rx="10"
                                    style="fill: #0b996c"
                                />
                                <path
                                    style="fill: #ffffff"
                                    d="M14,32V18c0-2.2,1.8-4,4-4h4c2.2,0,4,1.8,4,4s-1.8,4-4,4h-4v10H14z"
                                />
                                <circle cx="32" cy="26" r="7" style="fill: #ffffff" />
                            </svg>
                        </icon>
                        <div class="hc-item-main-content hc-flex-column">
                            <span v-text="$t('prospect.sms.via_brevo')"></span>
                            <span
                                v-if="prospect"
                                class="hc-prospect-sms-item-number"
                                v-text="prospect.mobile_phone_number"
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                </item-list>
            </template>

            <!-- List of sms -->
            <template #2>
                <frame-layout :count="3" :tab="frameTab" class="hc-flex-1">
                    <template #1>
                        <tab-layout
                            :count="2"
                            :tab="selectedProspectsTab"
                            class="hc-flex-1"
                        >
                            <template #1>
                                <div
                                    class="hc-flex-column"
                                    style="height: 100%"
                                >
                                    <!-- Back -->

                                    <item @click="tab = 0" class="bordered">
                                        <icon class="fa fa-caret-left" />
                                        <div
                                            class="hc-item-main-content"
                                            v-text="
                                                $t('prospect.sms.via_source', {
                                                    source,
                                                })
                                            "
                                        ></div>
                                        <icon
                                            v-if="sms.source == 'smsbox'"
                                            tag="a"
                                            class="fa fa-cog"
                                            @click.prevent.stop="
                                                configureSmsbox
                                            "
                                        />
                                        <icon
                                            v-else-if="sms.source == 'ultramsg'"
                                            tag="a"
                                            class="fa fa-cog"
                                            @click.prevent.stop="
                                                configureUltramsg
                                            "
                                        />
                                        <icon
                                            v-else-if="sms.source == 'mtarget'"
                                            tag="a"
                                            class="fa fa-cog"
                                            @click.prevent.stop="
                                                configureMTarget
                                            "
                                        />
                                        <icon
                                            v-else-if="sms.source == 'brevo'"
                                            tag="a"
                                            class="fa fa-cog"
                                            @click.prevent.stop="
                                                configureBrevo
                                            "
                                        />
                                    </item>

                                    <!-- Selected prospects -->

                                    <div
                                        id="hc-prospect-sms-selected-prospects"
                                        :class="
                                            selectedProspectsFolded
                                                ? 'folded'
                                                : ''
                                        "
                                    >
                                        <div
                                            id="hc-prospect-sms-selected-prospects-list"
                                        >
                                            <selected-prospect
                                                v-for="prospect in selectedProspects"
                                                :key="prospect.id"
                                                :prospect="prospect"
                                                @remove="removeSelectedProspect"
                                            />
                                        </div>
                                        <icon
                                            tag="a"
                                            :class="[
                                                'fa',
                                                selectedProspectsFolded
                                                    ? 'fa-caret-down'
                                                    : 'fa-caret-up',
                                            ]"
                                            @click.prevent.stop="
                                                selectedProspectsFolded =
                                                    !selectedProspectsFolded
                                            "
                                            size="30"
                                            style="min-width: 30px"
                                        />
                                        <icon
                                            tag="a"
                                            class="fa fa-plus"
                                            @click.prevent.stop="
                                                selectedProspectsTab = 1
                                            "
                                            size="30"
                                            style="min-width: 30px"
                                        />
                                    </div>

                                    <!-- List of SMS -->

                                    <div
                                        style="
                                            display: flex;
                                            flex-direction: column;
                                            width: 100%;
                                            flex: 1;
                                            position: relative;
                                        "
                                    >
                                        <div
                                            style="
                                                display: flex;
                                                flex-direction: column;
                                                width: 100%;
                                                text-align: right;
                                                max-height: 100%;
                                                padding: 15px;
                                                gap: 12px;
                                                position: absolute;
                                                bottom: 0;
                                                overflow: auto;
                                                scroll-behavior: smooth;
                                            "
                                            ref="sms"
                                        >
                                            <sms-row
                                                v-for="c in smss"
                                                :key="c.id"
                                                :sms="c"
                                            />
                                        </div>
                                        <loading :loading="fetchingSms" />
                                    </div>

                                    <!-- Sms form -->

                                    <form
                                        class="hc-flex-column"
                                        @submit.prevent="sendSMS"
                                        style="
                                            padding: 0 10px;
                                            position: relative;
                                        "
                                    >
                                        <!-- Sms textarea -->

                                        <textarea
                                            ref="textarea"
                                            id="hc-prospect-sms-textarea"
                                            :placeholder="
                                                $t(
                                                    'prospect.sms.enter_text_message'
                                                )
                                            "
                                            v-model="sms.message"
                                        ></textarea>

                                        <!-- Fold -->

                                        <div class="hc-flex-row">
                                            <icon
                                                v-if="showTextEditor"
                                                tag="a"
                                                class="fa fa-caret-down"
                                                v-tooltip="'Reduire'"
                                                @click="showTextEditor = false"
                                            />

                                            <!-- Unfold -->

                                            <icon
                                                v-else
                                                tag="a"
                                                class="fa fa-caret-up"
                                                v-tooltip="'Agrandir'"
                                                @click="showTextEditor = true"
                                            />

                                            <!-- Sms chars count -->

                                            <div
                                                class="hc-item-main-content hc-flex-column"
                                                style="
                                                    justify-content: center;
                                                    align-items: center;
                                                "
                                                v-text="charsCount + '/' + page"
                                            ></div>

                                            <!-- Sms templates -->

                                            <icon
                                                tag="a"
                                                class="fa fa-file-alt"
                                                v-tooltip="'Modèle de SMS'"
                                                @click.prevent="tab = 2"
                                            />

                                            <!-- Send -->

                                            <icon
                                                tag="button"
                                                class="fa fa-paper-plane"
                                                v-tooltip="'Envoyer'"
                                                style="color: #8641a6"
                                            />
                                        </div>
                                        <loading :loading="addingSms" />
                                    </form>
                                </div>
                            </template>

                            <!-- Add prospect to selected prospects -->

                            <template #2>
                                <select-prospect
                                    :search-fields="[
                                        'first_name',
                                        'last_name',
                                        'mobile_phone_number',
                                    ]"
                                    @back="selectedProspectsTab = 0"
                                    @prospect-selected="addSelectedProspect"
                            /></template>
                        </tab-layout>
                    </template>

                    <template #2 v-if="prospect">
                        <div class="hc-flex-column">
                            <item @click="tab = 0" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t('prospect.sms.via_source', {
                                            source,
                                        })
                                    "
                                ></div>
                            </item>
                            <ringover
                                id="ringover-sms"
                                :number="prospect.mobile_phone_number"
                                tab="sms"
                                style="flex: 1; width: 100%; height: 100%"
                                @sms-sent="sendSMSViaRingover"
                            />
                        </div>
                    </template>

                    <template #3>
                        <select-prospect
                            @back="tab = 0"
                            @prospect-selected="setSmsProspect"
                        />
                    </template>
                </frame-layout>
            </template>

            <template #3>
                <div class="hc-flex-column" style="height: 100%">
                    <item @click="tab = 1" class="bordered">
                        <icon class="fa fa-caret-left" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('prospect.sms.templates')"
                        ></div>
                    </item>
                    <search v-model="smsTemplateKeyword" />
                    <item-list class="hc-flex-1" padding="5px">
                        <template-row
                            v-for="c in filteredSmsTemplates"
                            :key="c.id"
                            :template="c"
                            @click="setSmsTemplate(c)"
                        />
                    </item-list>
                    <buttons>
                        <a
                            v-if="can('all.prospect.sms.add')"
                            @click.prevent="addSmsTemplate"
                            v-text="$t('add')"
                        ></a>
                    </buttons>
                </div>
            </template>
        </tab-layout>
    </slide>
</template>

<style>
.hc-prospect-sms-item {
    padding: 4px 0 !important;
    text-decoration: none;
}
.hc-prospect-sms-item-number {
    font-size: 11px;
    color: #999999;
}

#hc-prospect-sms-textarea {
    border: 1px solid #dddddd;
    border-radius: 5px;
    padding: 10px;
    max-height: 200px;
}

#hc-prospect-sms-selected-prospects {
    display: flex;
    flex-direction: row;
    border-bottom: 1px solid #eee;
    padding: 5px 5px 0 5px;
    max-height: 100%;
}

#hc-prospect-sms-selected-prospects-list {
    height: auto;
    overflow: auto;
    flex: 1;
}

.folded #hc-prospect-sms-selected-prospects-list {
    height: 35px;
    overflow: hidden;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import ProspectService from "@/apis/project/prospect";

import {
    SET_PROSPECT,
    UPDATE_SELECTED_PROSPECTS,
} from "@/actions/project/prospect";
import {
    FETCH_PROSPECT_SMSS,
    ADD_PROSPECT_SMS,
    BULK_PROSPECT_SMS,
} from "@/actions/project/prospect/sms";

import { OPEN_MODAL } from "@/actions/modal";
import { FETCH_SMS_TEMPLATES } from "@/actions/project/sms-template";

// Components
import Ringover from "@/components/utils/Ringover.vue";
import SmsRow from "./SmsRow.vue";
import TemplateRow from "./TemplateRow.vue";
import SelectedProspect from "./SelectedProspect.vue";
import SelectProspect from "../select/Select.vue";

export default {
    components: {
        Ringover,
        SelectProspect,
        SmsRow,
        TemplateRow,
        SelectedProspect,
    },

    data() {
        return {
            name: "prospect-manage-sms",
            tab: 0,
            frameTab: 0,
            selectedProspectsTab: 0,
            sms: this.newSMS(),
            smsTemplateKeyword: "",
            bulkSms: [],
            showTextEditor: true,
            selectedProspects: [],
            selectedProspectsFolded: true,
            fetchingSms: false,
            addingSms: false,
        };
    },

    methods: {
        newSMS() {
            return {
                message: "",
                source: "telephone",
            };
        },

        async fetchSms() {
            if (this.prospect) {
                this.fetchingSms = true;

                try {
                    await store.dispatch(FETCH_PROSPECT_SMSS);
                } finally {
                    this.fetchingSms = false;
                }
            } else if (this.prospectsSelected.length > 0) {
                this.bulkSms = [];
            } else {
                this.tab = 1;
                this.frameTab = 2;
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
                        fields: "first_name,last_name,mobile_phone_number",
                    },
                });
                this.selectedProspects = data.data.filter(
                    (prospect) => prospect.mobile_phone_number
                );
            } finally {
                this.fetchingProspect = false;
            }
        },

        addSelectedProspect(prospect) {
            this.selectedProspects = [prospect, ...this.selectedProspects];
            store.commit(
                UPDATE_SELECTED_PROSPECTS,
                this.selectedProspects.map((p) => p.id)
            );
            this.selectedProspectsTab = 0;
        },

        /**
         *
         */
        removeSelectedProspect(prospect) {
            this.selectedProspects = this.selectedProspects.filter(
                (p) => p.id != prospect.id
            );
            store.commit(
                UPDATE_SELECTED_PROSPECTS,
                this.selectedProspects.map((p) => p.id)
            );
        },

        sendSMSViaTelephone() {
            this.sms.source = "telephone";
            this.sms.message = "SMS via téléphone.";
            this.sendSMS();
        },

        sendSMSViaWhatsapp() {
            this.sms.source = "whatsapp";
            this.sms.message = "SMS via Whatsapp.";
            this.sendSMS();
        },

        sendSMSViaRingover(e) {
            this.sms.source = "ringover";
            this.sms.message = e.data.message;
            this.sendSMS();
        },

        async sendSMS() {
            this.addingSms = true;

            try {
                if (this.prospect) {
                    await store.dispatch(ADD_PROSPECT_SMS, this.sms);
                } else if (this.prospectsSelected.length > 0) {
                    await store.dispatch(BULK_PROSPECT_SMS, {
                        ...this.sms,
                        prospects: this.prospectsSelected,
                    });

                    this.bulkSms = [...this.bulkSms, { ...this.sms }];
                }
            } finally {
                this.addingSms = false;
                this.sms.message = "";
            }
        },

        configureSmsbox() {
            store.commit(OPEN_MODAL, "setting-smsbox");
        },

        configureUltramsg() {
            store.commit(OPEN_MODAL, "setting-ultramsg");
        },

        configureMTarget() {
            store.commit(OPEN_MODAL, "setting-mtarget");
        },

        configureBrevo() {
            store.commit(OPEN_MODAL, "setting-brevo");
        },

        /**
         *
         */
        addSmsTemplate() {
            store.commit(OPEN_MODAL, "sms-template-add");
        },

        /**
         *
         */
        setSmsTemplate(template) {
            this.sms.message = template.body;
            this.tab = 1;
        },

        /**
         *
         */
        fetchSmsTemplates() {
            store.dispatch(FETCH_SMS_TEMPLATES);
        },

        /**
         *
         */
        setSmsProspect(prospect) {
            store.commit(SET_PROSPECT, prospect);
            this.tab = 0;
        },
    },

    watch: {
        async prospectSms(newValue, oldValue) {
            await this.$nextTick();

            if (newValue.length != oldValue.length && this.$refs.sms) {
                this.$refs.sms.scrollTop = this.$refs.sms.scrollHeight;
            }
        },

        async tab(newValue) {
            await this.$nextTick();

            if (newValue == 1) {
                if (this.$refs.sms) {
                    this.$refs.sms.scrollTop = this.$refs.sms.scrollHeight;
                }
            } else if (newValue == 2) {
                this.fetchSmsTemplates();
            }
        },

        async prospect(newValue) {
            if (newValue && this.slideOpen(this.name)) {
                this.fetchSms();
            }
        },

        async smsBody(newValue, oldValue) {
            await this.$nextTick();

            if (newValue.length != oldValue.length) {
                this.$refs.textarea.style.height = 0;
                this.$refs.textarea.style.height =
                    this.$refs.textarea.scrollHeight + "px";
            }
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "prospect",
            "prospectsSelected",
            "prospectFullName",
            "prospectSms",
            "slideOpen",
            "smsTemplates",
            "can",
        ]),

        /**
         *
         */
        filteredSmsTemplates() {
            const keyword = removeStringAccent(this.smsTemplateKeyword);

            return this.smsTemplates.filter(
                (smsTemplate) =>
                    removeStringAccent(smsTemplate.name).indexOf(keyword) >= 0
            );
        },

        /**
         * Sms source
         */
        source() {
            return this.sms.source;
        },

        /**
         * Sms body
         */
        smsBody() {
            return this.sms.message;
        },

        /**
         * Characters count
         * for the current sms page
         */
        charsCount: function () {
            return this.sms.message.length % 160;
        },

        /**
         * Current sms page
         */
        page: function () {
            return parseInt(this.sms.message.length / 160) + 1;
        },

        /**
         * Whatapp app url
         */
        whatsappUrl: function () {
            return "https://wa.me/" + this.prospect.mobile_phone_number;
        },

        /**
         *
         */
        smss() {
            return this.prospect ? this.prospectSms : this.bulkSms;
        },
    },
};
</script>