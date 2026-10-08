<template>
    <frame-layout :count="7" :tab="frameTab" class="hc-flex-1">
        <template #1 v-if="interactionProspect">
            <aircall-frame
                :interaction="interaction"
                :tab="tab"
                :frame-tab="frameTab"
                :aircall-tab="aircallTab"
                :aircall-webhook-url="aircallWebhookUrl"
                @back="$emit('back')"
                @setting="$emit('aircall-setting')"
                @back-setting="$emit('aircall-back-setting')"
                @copy-webhook="$emit('copy-aircall-webhook')"
                @update-interaction="$emit('update-interaction')"
                @next-interaction="$emit('next-interaction')"
            />
        </template>

        <template #2 v-if="interactionProspect">
            <ringover-frame
                :interaction="interaction"
                @back="$emit('back')"
                @setting="$emit('ringover-setting')"
                @update-interaction="$emit('update-interaction')"
                @next-interaction="$emit('next-interaction')"
            />
        </template>

        <template #3>
            <select-prospect
                @back="$emit('back')"
                @prospect-selected="$emit('prospect-selected', $event)"
            />
        </template>

        <template #4>
            <phone-number-form
                title-key="prospect.interaction.edit_phone_number"
                :model-value="phoneNumber"
                :loading="updatingPhoneNumber"
                @back="$emit('back')"
                @submit="$emit('update-phone-number')"
                @update:model-value="$emit('update:phoneNumber', $event)"
            />
        </template>

        <template #5>
            <phone-number-form
                title-key="prospect.interaction.edit_mobile_phone_number"
                :model-value="mobilePhoneNumber"
                :loading="updatingMobilePhoneNumber"
                :relative="true"
                @back="$emit('back')"
                @submit="$emit('update-mobile-phone-number')"
                @update:model-value="$emit('update:mobilePhoneNumber', $event)"
            />
        </template>

        <template #6 v-if="interactionProspect">
            <cloudtalk-frame @back="$emit('back-cloudtalk')" />
        </template>

        <template #7 v-if="interactionProspect">
            <kavkom-frame
                :number="interaction.number"
                :project-id="projectId"
                :ready="kavkomReady"
                :calling="callingKavkom"
                :message="kavkomMessage"
                :success="kavkomSuccess"
                @back="$emit('back')"
                @call="$emit('call-kavkom')"
            />
        </template>
    </frame-layout>
</template>

<script>
import SelectProspect from "../../select/Select.vue";
import AircallFrame from "./AircallFrame.vue";
import RingoverFrame from "./RingoverFrame.vue";
import PhoneNumberForm from "./PhoneNumberForm.vue";
import CloudtalkFrame from "./CloudtalkFrame.vue";
import KavkomFrame from "./KavkomFrame.vue";

export default {
    components: {
        SelectProspect,
        AircallFrame,
        RingoverFrame,
        PhoneNumberForm,
        CloudtalkFrame,
        KavkomFrame,
    },

    props: {
        interactionProspect: {
            type: Object,
            default: null,
        },

        interaction: {
            type: Object,
            required: true,
        },

        tab: {
            type: Number,
            required: true,
        },

        frameTab: {
            type: Number,
            required: true,
        },

        aircallTab: {
            type: Number,
            required: true,
        },

        aircallWebhookUrl: {
            type: String,
            required: true,
        },

        phoneNumber: {
            type: String,
            default: null,
        },

        mobilePhoneNumber: {
            type: String,
            default: null,
        },

        updatingPhoneNumber: {
            type: Boolean,
            default: false,
        },

        updatingMobilePhoneNumber: {
            type: Boolean,
            default: false,
        },

        projectId: {
            type: [Number, String],
            default: null,
        },

        kavkomReady: {
            type: Boolean,
            default: false,
        },

        callingKavkom: {
            type: Boolean,
            default: false,
        },

        kavkomMessage: {
            type: String,
            default: "",
        },

        kavkomSuccess: {
            type: Boolean,
            default: false,
        },
    },

    emits: [
        "back",
        "aircall-setting",
        "aircall-back-setting",
        "copy-aircall-webhook",
        "ringover-setting",
        "update-interaction",
        "next-interaction",
        "prospect-selected",
        "update-phone-number",
        "update-mobile-phone-number",
        "update:phoneNumber",
        "update:mobilePhoneNumber",
        "back-cloudtalk",
        "call-kavkom",
    ],
};
</script>
