<template>
    <div
        :class="[
            'hc-prospect-interaction-cloudtalk-panel',
            {
                visible: displayed,
                'with-context': contextVisible,
                'context-open': contextVisible && contextPanelVisible,
            },
        ]"
    >
        <cloudtalk
            id="cloudtalk-phone"
            class="hc-prospect-interaction-cloudtalk-phone"
            :number="number"
            :calling="calling"
            :loading="loading"
            @make-call="emitPanelEvent('make-call')"
            @call-activity="emitPanelEvent('call-activity', $event)"
            @ringing-call="emitPanelEvent('ringing-call', $event)"
            @outgoing-call="emitPanelEvent('outgoing-call', $event)"
            @call-ended="emitPanelEvent('call-ended', $event)"
            @hangup-call="emitPanelEvent('hangup-call', $event)"
            @answered-call="emitPanelEvent('answered-call', $event)"
            @contact-info="emitPanelEvent('contact-info', $event)"
        />

        <button
            v-if="contextVisible"
            type="button"
            class="hc-prospect-interaction-cloudtalk-context-toggle"
            :aria-expanded="contextPanelVisible ? 'true' : 'false'"
            :title="
                contextPanelVisible
                    ? 'Masquer le contexte'
                    : 'Afficher le contexte'
            "
            @click="toggleContextPanel"
        >
            <icon class="fa fa-caret-right" />
        </button>

        <aside
            v-if="contextVisible"
            v-show="contextPanelVisible"
            class="hc-prospect-interaction-cloudtalk-context"
        >
            <div
                v-if="lookupLoading && !prospect"
                class="hc-prospect-interaction-cloudtalk-context-loading"
            >
                <loading :loading="lookupLoading" />
            </div>

            <template v-else-if="prospect">
                <div class="hc-prospect-interaction-cloudtalk-context-header">
                    <div>
                        <div
                            class="hc-prospect-interaction-cloudtalk-context-title"
                            v-text="prospectName"
                        ></div>
                        <div
                            v-if="prospect.company_name"
                            class="hc-prospect-interaction-cloudtalk-context-subtitle"
                            v-text="prospect.company_name"
                        ></div>
                    </div>
                    <router-link
                        class="hc-prospect-interaction-cloudtalk-context-link"
                        :to="{
                            name: 'prospect.show',
                            params: {
                                project: project.slug,
                                prospect: prospect.id,
                            },
                        }"
                    >
                        <icon class="fa fa-external-link" />
                    </router-link>
                </div>

                <div
                    v-if="shouldShowContextNumber || hasDifferentContextNumber"
                    class="hc-prospect-interaction-cloudtalk-context-section"
                >
                    <div
                        v-if="shouldShowContextNumber"
                        class="hc-prospect-interaction-cloudtalk-context-heading"
                    >
                        Numero CloudTalk
                    </div>
                    <div
                        v-if="shouldShowContextNumber"
                        class="hc-prospect-interaction-cloudtalk-context-line"
                    >
                        <icon class="fa fa-phone" />
                        <span v-text="contextNumber"></span>
                    </div>
                    <div
                        v-if="hasDifferentContextNumber"
                        class="hc-prospect-interaction-cloudtalk-context-empty"
                    >
                        Le numero CloudTalk est different du numero du prospect.
                    </div>
                    <button
                        v-if="hasDifferentContextNumber && canCreateProspect"
                        type="button"
                        class="hc-prospect-interaction-cloudtalk-context-button"
                        :disabled="creatingProspect"
                        @click="emitPanelEvent('create-prospect')"
                    >
                        <icon class="fa fa-plus" />
                        <span v-text="'Creer un prospect avec ce numero'"></span>
                        <loading :loading="creatingProspect" />
                    </button>
                </div>

                <div class="hc-prospect-interaction-cloudtalk-context-section">
                    <div class="hc-prospect-interaction-cloudtalk-context-heading">
                        Prospect
                    </div>
                    <div
                        v-if="prospect.email"
                        class="hc-prospect-interaction-cloudtalk-context-line"
                    >
                        <icon class="fa fa-envelope" />
                        <span v-text="prospect.email"></span>
                    </div>
                    <div
                        v-if="prospect.phone_number"
                        class="hc-prospect-interaction-cloudtalk-context-line"
                    >
                        <icon class="fa fa-phone" />
                        <span v-text="prospect.phone_number"></span>
                    </div>
                    <div
                        v-if="prospect.mobile_phone_number"
                        class="hc-prospect-interaction-cloudtalk-context-line"
                    >
                        <icon class="fa fa-mobile" />
                        <span v-text="prospect.mobile_phone_number"></span>
                    </div>
                </div>

                <div class="hc-prospect-interaction-cloudtalk-context-section">
                    <div class="hc-prospect-interaction-cloudtalk-context-heading">
                        information
                    </div>
                    <div
                        v-if="threads.length == 0"
                        class="hc-prospect-interaction-cloudtalk-context-empty"
                    >
                        Aucun information lie a votre utilisateur.
                    </div>
                    <div
                        v-for="thread in threads"
                        :key="thread.id"
                        class="hc-prospect-interaction-cloudtalk-thread"
                    >
                        <div class="hc-prospect-interaction-cloudtalk-thread-title">
                            <span
                                class="hc-prospect-interaction-cloudtalk-thread-color"
                                :style="{
                                    color: thread.color,
                                    backgroundColor: thread.bgcolor,
                                }"
                            ></span>
                            <span v-text="thread.name"></span>
                            <small
                                v-text="
                                    thread.user_messages_count +
                                    '/' +
                                    thread.messages_count
                                "
                            ></small>
                        </div>

                        <div
                            v-for="message in messagesForThread(thread)"
                            :key="message.id"
                            class="hc-prospect-interaction-cloudtalk-message"
                        >
                            <div class="hc-prospect-interaction-cloudtalk-message-meta">
                                <span
                                    v-text="
                                        message.creator
                                            ? message.creator.name
                                            : ''
                                    "
                                ></span>
                                <span
                                    v-text="formatDate(message.created_at)"
                                ></span>
                            </div>
                            <div
                                class="hc-prospect-interaction-cloudtalk-message-body"
                                v-text="messagePreview(message.body)"
                            ></div>
                            <div
                                v-if="message.users && message.users.length"
                                class="hc-prospect-interaction-cloudtalk-message-users"
                            >
                                <icon class="fa fa-user" />
                                <span
                                    v-text="
                                        message.users
                                            .map((user) => user.name)
                                            .join(', ')
                                    "
                                ></span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template v-else>
                <div class="hc-prospect-interaction-cloudtalk-context-section">
                    <div class="hc-prospect-interaction-cloudtalk-context-heading">
                        Numero CloudTalk
                    </div>
                    <div
                        v-if="contextNumber"
                        class="hc-prospect-interaction-cloudtalk-context-line"
                    >
                        <icon class="fa fa-phone" />
                        <span v-text="contextNumber"></span>
                    </div>
                    <div class="hc-prospect-interaction-cloudtalk-context-empty">
                        Aucun prospect lie a ce numero.
                    </div>
                    <button
                        v-if="canCreateProspect"
                        type="button"
                        class="hc-prospect-interaction-cloudtalk-context-button"
                        :disabled="creatingProspect"
                        @click="emitPanelEvent('create-prospect')"
                    >
                        <icon class="fa fa-plus" />
                        <span v-text="'Creer un prospect avec ce numero'"></span>
                        <loading :loading="creatingProspect" />
                    </button>
                </div>
            </template>
        </aside>
    </div>
</template>

<script>
import Cloudtalk from "@/components/utils/Cloudtalk.vue";

export default {
    components: {
        Cloudtalk,
    },

    props: {
        displayed: {
            type: Boolean,
            default: false,
        },

        contextVisible: {
            type: Boolean,
            default: false,
        },

        number: {
            type: [String, Number],
            default: "",
        },

        lookupNumber: {
            type: [String, Number],
            default: "",
        },

        calling: {
            type: Boolean,
            default: false,
        },

        loading: {
            type: Boolean,
            default: false,
        },

        lookupLoading: {
            type: Boolean,
            default: false,
        },

        prospect: {
            type: Object,
            default: null,
        },

        prospectName: {
            type: String,
            default: "",
        },

        project: {
            type: Object,
            required: true,
        },

        threads: {
            type: Array,
            default: () => [],
        },

        messagesForThread: {
            type: Function,
            required: true,
        },

        formatDate: {
            type: Function,
            required: true,
        },

        messagePreview: {
            type: Function,
            required: true,
        },

        creatingProspect: {
            type: Boolean,
            default: false,
        },

        canCreateProspect: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            contextPanelVisible: false,
        };
    },

    computed: {
        contextNumber() {
            return this.lookupNumber || this.number;
        },

        hasDifferentContextNumber() {
            if (!this.prospect || !this.contextNumber) {
                return false;
            }

            const contextNumber = this.normalizePhone(this.contextNumber);

            return (
                contextNumber &&
                this.prospectPhoneNumbers.length > 0 &&
                !this.prospectPhoneNumbers.includes(contextNumber)
            );
        },

        prospectPhoneNumbers() {
            if (!this.prospect) {
                return [];
            }

            return [
                this.prospect.phone_number,
                this.prospect.mobile_phone_number,
            ]
                .filter((number) => number)
                .map((number) => this.normalizePhone(number));
        },

        shouldShowContextNumber() {
            return (
                this.contextNumber &&
                (!this.prospectPhoneNumbers.length ||
                    this.hasDifferentContextNumber)
            );
        },
    },

    watch: {
        contextVisible(value) {
            if (!value) {
                this.closeContextPanel();
            }
        },

        displayed(value) {
            if (!value) {
                this.closeContextPanel();
            }
        },

        lookupNumber() {
            this.closeContextPanel();
        },

        number() {
            this.closeContextPanel();
        },
    },

    methods: {
        toggleContextPanel() {
            this.setContextPanelVisible(!this.contextPanelVisible);
        },

        closeContextPanel() {
            this.setContextPanelVisible(false);
        },

        setContextPanelVisible(visible) {
            const nextValue = this.contextVisible && visible;

            if (this.contextPanelVisible == nextValue) {
                return;
            }

            this.contextPanelVisible = nextValue;
            this.$emit("context-panel-visible", nextValue);
        },

        emitPanelEvent(eventName, payload) {
            this.closeContextPanel();
            this.$emit(eventName, payload);
        },

        normalizePhone(number) {
            return String(number || "").replace(/\D+/g, "");
        },
    },

    emits: [
        "make-call",
        "call-activity",
        "ringing-call",
        "outgoing-call",
        "call-ended",
        "hangup-call",
        "answered-call",
        "contact-info",
        "create-prospect",
        "context-panel-visible",
    ],
};
</script>
