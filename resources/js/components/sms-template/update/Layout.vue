<template>
    <form
        class="hc-flex-column"
        style="height: 100%"
        v-if="smsTemplateToUpdate"
        @submit.prevent="update"
    >
        <item-list gap="5px" class="hc-flex-1" padding="10px 0">
            <v-field :label="$t('name')" required v-slot="{ label }"
                ><input
                    :placeholder="label + ' ...'"
                    v-model="smsTemplateToUpdate.name"
                    required
            /></v-field>
            <v-field :label="$t('content')" required v-slot="{ label }"
                ><autocomplete
                    :placeholder="label + ' ...'"
                    :strategies="strategies"
                    v-model="smsTemplateToUpdate.body"
                    required
            /></v-field>
        </item-list>
        <buttons>
            <button
                v-if="can('all.project.sms-template.delete')"
                @click.prevent="remove"
                class="hc-button-danger"
                v-text="$t('delete')"
            ></button>
            <button v-text="$t('update')"></button>
        </buttons>
        <loading :loading="updatingSmsTemplate || removingSmsTemplate" />
    </form>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    SHOW_SMS_TEMPLATE,
    UPDATE_SMS_TEMPLATE,
    REMOVE_SMS_TEMPLATE,
} from "@/actions/project/sms-template";
import { CLOSE_MODAL } from "@/actions/modal";
import { FETCH_PROSPECTS } from "@/actions/project/prospect";

export default {
    data() {
        return {
            updatingSmsTemplate: false,
            removingSmsTemplate: false,
            fetchingSmsTemplate: false,
            smsTemplateToUpdate: this.smsTemplate,
        };
    },

    methods: {
        /**
         *
         */
        async update() {
            this.updatingSmsTemplate = true;

            try {
                await store.dispatch(
                    UPDATE_SMS_TEMPLATE,
                    this.smsTemplateToUpdate
                );
                store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.updatingSmsTemplate = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingSmsTemplate = true;

                try {
                    await store.dispatch(
                        REMOVE_SMS_TEMPLATE,
                        this.smsTemplateToUpdate.id
                    );
                    store.dispatch(FETCH_PROSPECTS);
                } finally {
                    this.removingSmsTemplate = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },
    },

    watch: {
        async smsTemplate(newValue) {
            if (newValue) {
                this.smsTemplateToUpdate = { ...newValue };

                /*this.fetchingSmsTemplate = true;
                this.smsTemplateToUpdate = await store.dispatch(
                    SHOW_SMS_TEMPLATE,
                    newValue.id
                );
                this.fetchingSmsTemplate = false;*/
            }
        },
    },

    computed: {
        ...mapGetters(["smsTemplate", "fields", "can"]),
    },
};
</script>
