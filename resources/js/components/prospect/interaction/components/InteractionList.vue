<template>
    <div class="hc-prospect-interaction-list">
        <item-list class="hc-prospect-interaction-list-content" padding="12px">
            <template v-if="canAdd">
                <item
                    v-if="interactionProspect.phone_number"
                    @click.prevent="$emit('edit-phone')"
                >
                    <icon class="fa fa-phone" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('prospect.interaction.edit_phone_number')"
                    ></div>
                    <icon class="fa fa-caret-right" />
                </item>
                <item
                    v-if="interactionProspect.mobile_phone_number"
                    @click.prevent="$emit('edit-mobile')"
                >
                    <icon class="fa fa-mobile" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('prospect.interaction.edit_mobile_phone_number')"
                    ></div>
                    <icon class="fa fa-caret-right" />
                </item>

                <template v-for="number in interactionNumbers" :key="number">
                    <item
                        tag="a"
                        class="hc-prospect-interaction-item"
                        @click="$emit('call-telephone', number)"
                        :href="'tel:' + number"
                    >
                        <icon class="fa fa-phone" color="#489f1f" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span v-text="$t('prospect.interaction.call_by_phone')"></span>
                            <span
                                class="hc-prospect-interaction-item-number"
                                v-text="number"
                            ></span>
                        </div>
                    </item>

                    <div
                        v-for="operator in topOperators"
                        :key="operator.key"
                        class="hc-prospect-interaction-operator-entry"
                    >
                        <item
                            class="hc-prospect-interaction-item"
                            @click="$emit(operator.event, number)"
                        >
                            <icon class="hc-prospect-interaction-provider-icon">
                                <img :src="operator.icon" alt="" />
                            </icon>
                            <div class="hc-item-main-content hc-flex-column">
                                <span v-text="operator.label"></span>
                                <span
                                    class="hc-prospect-interaction-item-number"
                                    v-text="number"
                                ></span>
                            </div>
                            <icon class="fa fa-caret-right" />
                        </item>
                        <button
                            type="button"
                            class="hc-prospect-interaction-move-operator"
                            :title="'Déplacer ' + operator.label + ' en bas'"
                            :aria-label="'Déplacer ' + operator.label + ' en bas'"
                            @click.stop="setOperatorPinned(operator.key, false)"
                        >
                            <icon class="fa fa-chevron-down" />
                        </button>
                    </div>
                </template>

                <item
                    tag="a"
                    class="hc-prospect-interaction-item"
                    @click="$emit('add-history')"
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
                v-if="prospectInteractions.length > 0"
                class="hc-prospect-interaction-history-title"
            >
                <icon class="fa fa-clock" color="white"></icon>
                <div
                    class="hc-main-content"
                    v-text="$t('prospect.interaction.history')"
                ></div>
            </item>
            <interaction-row
                v-for="(interaction, index) in prospectInteractions"
                :key="interaction.id"
                :interaction="interaction"
                :color-index="index"
            />
        </item-list>

        <div
            v-if="canAdd && interactionNumbers.length && secondaryOperators.length"
            class="hc-prospect-interaction-operator-dock"
            role="toolbar"
            aria-label="Autres opérateurs d’appel"
        >
            <div
                v-for="number in interactionNumbers"
                :key="number"
                class="hc-prospect-interaction-operator-row"
            >
                <span
                    v-if="interactionNumbers.length > 1"
                    class="hc-prospect-interaction-dock-number"
                    v-text="number"
                ></span>
                <div class="hc-prospect-interaction-operator-buttons">
                    <button
                        v-for="operator in secondaryOperators"
                        :key="operator.key"
                        type="button"
                        class="hc-prospect-interaction-operator-button"
                        :title="'Afficher ' + operator.label + ' en haut — ' + number"
                        :aria-label="'Afficher ' + operator.label + ' en haut — ' + number"
                        @click="setOperatorPinned(operator.key, true)"
                    >
                        <img :src="operator.icon" alt="" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.hc-prospect-interaction-list {
    display: flex;
    flex: 1;
    flex-direction: column;
    min-height: 0;
    height: 100%;
}

.hc-prospect-interaction-list .hc-prospect-interaction-list-content {
    flex: 1;
    min-height: 0;
    height: auto;
    overflow-y: auto;
}

.hc-prospect-interaction-provider-icon img {
    display: block;
    max-width: 26px;
    max-height: 26px;
    object-fit: contain;
}

.hc-prospect-interaction-operator-entry {
    display: flex;
    align-items: center;
    gap: 3px;
    width: 100%;
}

.hc-prospect-interaction-operator-entry > .hc-item {
    flex: 1;
    min-width: 0;
    width: auto;
}

.hc-prospect-interaction-move-operator {
    display: inline-flex;
    flex: 0 0 28px;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    padding: 0;
    border: 0;
    border-radius: 50%;
    background: transparent;
    color: #888888;
    cursor: pointer;
}

.hc-prospect-interaction-move-operator:hover,
.hc-prospect-interaction-move-operator:focus-visible {
    background: #f1f3f4;
    color: #333333;
}

.hc-prospect-interaction-move-operator:focus-visible {
    outline: 2px solid #12a0f3;
    outline-offset: 1px;
}

.hc-prospect-interaction-history-title {
    margin-top: 10px;
    margin-bottom: 10px;
    background-color: #7939b8 !important;
    color: #ffffff !important;
}

.hc-prospect-interaction-operator-dock {
    display: flex;
    flex: 0 0 auto;
    flex-direction: column;
    gap: 6px;
    max-height: 132px;
    overflow-y: auto;
    padding: 7px 12px;
    border-top: 1px solid #eeeeee;
    background: #ffffff;
}

.hc-prospect-interaction-operator-row {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 3px;
}

.hc-prospect-interaction-dock-number {
    width: 100%;
    overflow: hidden;
    color: #777777;
    font-size: 11px;
    text-overflow: ellipsis;
    text-align: center;
    white-space: nowrap;
}

.hc-prospect-interaction-operator-buttons {
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.hc-prospect-interaction-operator-button {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    padding: 3px;
    border: 0;
    border-radius: 6px;
    background: transparent;
    cursor: pointer;
}

.hc-prospect-interaction-operator-button::after {
    position: absolute;
    right: -3px;
    bottom: -2px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #ffffff;
    color: #5f6368;
    content: "+";
    font-size: 11px;
    line-height: 1;
}

.hc-prospect-interaction-operator-button:hover,
.hc-prospect-interaction-operator-button:focus-visible {
    background: #f1f3f4;
}

.hc-prospect-interaction-operator-button:focus-visible {
    outline: 2px solid #12a0f3;
    outline-offset: 1px;
}

.hc-prospect-interaction-operator-button img {
    display: block;
    width: 24px;
    height: 24px;
    object-fit: contain;
}
</style>

<script>
import InteractionRow from "../InteractionRow.vue";

export default {
    components: {
        InteractionRow,
    },

    props: {
        interactionProspect: {
            type: Object,
            required: true,
        },

        prospectInteractions: {
            type: Array,
            default: () => [],
        },

        addingHistory: {
            type: Boolean,
            default: false,
        },

        canAdd: {
            type: Boolean,
            default: false,
        },

        cloudtalkConfigured: {
            type: Boolean,
            default: false,
        },

        kavkomConfigured: {
            type: Boolean,
            default: false,
        },

        projectSlug: {
            type: String,
            default: "",
        },

        userId: {
            type: [Number, String],
            default: null,
        },
    },

    data() {
        return {
            operatorPreferences: {},
        };
    },

    emits: [
        "edit-phone",
        "edit-mobile",
        "call-telephone",
        "call-aircall",
        "call-ringover",
        "call-cloudtalk",
        "call-kavkom",
        "add-history",
    ],

    watch: {
        operatorStorageKey: {
            immediate: true,
            handler() {
                this.loadOperatorPreferences();
            },
        },
    },

    computed: {
        operatorStorageKey() {
            return `heroes-crm:prospect-interaction-operators:${this.projectSlug || "none"}:${this.userId || "none"}`;
        },

        interactionNumbers() {
            return [
                this.interactionProspect.phone_number,
                this.interactionProspect.mobile_phone_number,
            ].reduce((numbers, number) => {
                if (!number || numbers.includes(number)) {
                    return numbers;
                }

                return [...numbers, number];
            }, []);
        },

        operatorOptions() {
            return [
                {
                    key: "aircall",
                    label: this.$t("prospect.interaction.call_by_aircall"),
                    icon: "/images/partenaire-ext/aircall.png",
                    event: "call-aircall",
                },
                {
                    key: "ringover",
                    label: this.$t("prospect.interaction.call_by_ringover"),
                    icon: "/images/partenaire-ext/ringover.png",
                    event: "call-ringover",
                },
                {
                    key: "cloudtalk",
                    label: this.$t("prospect.interaction.call_by_cloudtalk"),
                    icon: "/images/partenaire-ext/cloudtalk.png",
                    event: "call-cloudtalk",
                },
                {
                    key: "kavkom",
                    label: this.$t("prospect.interaction.call_by_kavkom"),
                    icon: "/images/partenaire-ext/kavkom.png",
                    event: "call-kavkom",
                },
            ];
        },

        topOperators() {
            return this.operatorOptions.filter((operator) =>
                this.isOperatorPinned(operator.key)
            );
        },

        secondaryOperators() {
            return this.operatorOptions.filter(
                (operator) => !this.isOperatorPinned(operator.key)
            );
        },
    },

    methods: {
        isOperatorPinned(key) {
            if (Object.prototype.hasOwnProperty.call(this.operatorPreferences, key)) {
                return this.operatorPreferences[key];
            }

            if (key === "cloudtalk") return this.cloudtalkConfigured;
            if (key === "kavkom") return this.kavkomConfigured;

            return false;
        },

        loadOperatorPreferences() {
            const validOperators = new Set(["aircall", "ringover", "cloudtalk", "kavkom"]);
            let preferences = {};

            try {
                const saved = window.localStorage.getItem(this.operatorStorageKey);
                const parsed = saved ? JSON.parse(saved) : {};

                if (parsed && typeof parsed === "object" && !Array.isArray(parsed)) {
                    preferences = Object.fromEntries(
                        Object.entries(parsed).filter(
                            ([key, value]) => validOperators.has(key) && typeof value === "boolean"
                        )
                    );
                }
            } catch (error) {
                preferences = {};
            }

            this.operatorPreferences = preferences;
        },

        setOperatorPinned(key, pinned) {
            if (!["aircall", "ringover", "cloudtalk", "kavkom"].includes(key)) {
                return;
            }

            this.operatorPreferences = {
                ...this.operatorPreferences,
                [key]: pinned,
            };

            try {
                window.localStorage.setItem(
                    this.operatorStorageKey,
                    JSON.stringify(this.operatorPreferences)
                );
            } catch (error) {
                // Keep the current-session preference if browser storage is unavailable.
            }
        },
    },
};
</script>
