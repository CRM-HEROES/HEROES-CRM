<template>
    <item-list style="height: 100%; overflow: auto" padding="12px">
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
                    v-text="
                        $t('prospect.interaction.edit_mobile_phone_number')
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
                    @click="$emit('call-telephone', number)"
                    :href="'tel:' + number"
                >
                    <icon class="fa fa-phone" color="#489f1f" />
                    <div class="hc-item-main-content hc-flex-column">
                        <span
                            v-text="$t('prospect.interaction.call_by_phone')"
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
                    @click="$emit('call-aircall', number)"
                >
                    <icon>
                        <svg viewBox="0 0 40 40">
                            <path
                                fill="#00B388"
                                d="M39.1,9.8c-0.9-4.5-4.5-8-9-9C27.9,0.3,24.2,0,20,0S12.1,0.3,9.9,0.8c-4.5,0.9-8,4.5-9,9 c-0.5,2.3-0.9,6-0.9,10.2c0,4.2,0.3,7.9,0.9,10.2c0.9,4.5,4.5,8,9,9C12.1,39.6,15.8,40,20,40s7.9-0.3,10.1-0.9c4.5-0.9,8-4.5,9-9 c0.5-2.3,0.9-6,0.9-10.2C39.9,15.8,39.6,12.1,39.1,9.8z M29.3,30.5C29.3,30.5,29.3,30.5,29.3,30.5c-0.7,0.3-1.9,0.5-3.5,0.6 c-0.1,0-0.1,0-0.2,0c-0.3,0-0.6-0.2-0.8-0.5c-0.4-0.9-1.2-1.6-2.2-1.8c-0.6-0.1-1.5-0.2-2.6-0.2s-2,0.1-2.6,0.2 c-1,0.2-1.8,0.9-2.2,1.8c-0.1,0.3-0.4,0.5-0.8,0.5c-0.1,0-0.2,0-0.2,0c-1.6-0.2-2.8-0.4-3.5-0.6c0,0,0,0,0,0 c-0.5-0.2-0.8-0.6-0.8-1.2c0,0,0,0,0,0c0,0,0,0,0-0.1c0,0,0,0,0,0c0,0,0,0,0,0c0.1-1.6,1.1-5.5,2.6-9.8c1.7-5,3.5-9,4.2-9.8 c0.1-0.1,0.3-0.2,0.4-0.3c0.1,0,0.1-0.1,0.2-0.1c0,0,0,0,0,0c0.5-0.2,1.5-0.3,2.6-0.3c1.1,0,2.1,0.1,2.6,0.3c0,0,0,0,0,0 c0.1,0,0.2,0.1,0.2,0.1c0.2,0.1,0.3,0.2,0.4,0.3c0,0,0,0,0,0c0.8,0.8,2.6,4.8,4.2,9.8c1.5,4.4,2.5,8.2,2.6,9.8c0,0,0,0,0,0 c0,0,0,0,0,0c0,0,0,0,0,0.1c0,0,0,0,0,0C30.1,29.8,29.8,30.3,29.3,30.5z"
                            ></path>
                        </svg>
                    </icon>
                    <div class="hc-item-main-content hc-flex-column">
                        <span
                            v-text="$t('prospect.interaction.call_by_aircall')"
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
                    @click="$emit('call-ringover', number)"
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
                            v-text="$t('prospect.interaction.call_by_ringover')"
                        ></span>
                        <span
                            class="hc-prospect-interaction-item-number"
                            v-text="number"
                        ></span>
                    </div>
                    <icon class="fa fa-caret-right" />
                </item>

                <!-- CloudTalk -->
                <item
                    class="hc-prospect-interaction-item"
                    @click="$emit('call-cloudtalk', number)"
                >
                    <icon>
                        <svg viewBox="0 0 40 40">
                            <path
                                fill="#1f6feb"
                                d="M30.8,18.6c-0.6-1.4-1.9-2.3-3.4-2.3c-0.2,0-0.4,0-0.6,0.1C26.2,12.5,22.6,9.6,18.3,9.6c-4.9,0-9,3.8-9.5,8.6c-0.2,0-0.4-0.1-0.6-0.1c-3.1,0-5.6,2.5-5.6,5.6c0,3.1,2.5,5.6,5.6,5.6h20.6c2.9,0,5.2-2.3,5.2-5.2C34.1,21.4,32.8,19.4,30.8,18.6z"
                            ></path>
                            <path
                                fill="#ffffff"
                                d="M22.8,25.9c-1.6-0.8-2.9-2.1-3.7-3.7c-0.1-0.2-0.1-0.5,0.1-0.7l0.7-0.7c0.3-0.3,0.3-0.8,0.1-1.1l-1.1-1.5c-0.2-0.3-0.6-0.4-0.9-0.3c-1.1,0.4-1.9,1.3-2.1,2.4c-0.2,1.4,0.3,2.9,1.2,4.2c1,1.5,2.6,2.6,4.3,3.1c1,0.3,2.1,0.1,2.9-0.5c0.4-0.3,0.5-0.8,0.3-1.2l-0.9-1.6C23.6,26,23.2,25.8,22.8,25.9z"
                            ></path>
                        </svg>
                    </icon>
                    <div class="hc-item-main-content hc-flex-column">
                        <span
                            v-text="
                                $t('prospect.interaction.call_by_cloudtalk')
                            "
                        ></span>
                        <span
                            class="hc-prospect-interaction-item-number"
                            v-text="number"
                        ></span>
                    </div>
                    <icon class="fa fa-caret-right" />
                </item>

                <!-- Kavkom -->
                <item
                    class="hc-prospect-interaction-item"
                    @click="$emit('call-kavkom', number)"
                >
                    <icon class="fa fa-phone" color="#8e24aa" />
                    <div class="hc-item-main-content hc-flex-column">
                        <span
                            v-text="$t('prospect.interaction.call_by_kavkom')"
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
};
</script>
