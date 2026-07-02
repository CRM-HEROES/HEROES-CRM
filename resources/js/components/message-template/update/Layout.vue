<template>
    <form
        class="hc-flex-column"
        style="height: 100%"
        v-if="messageTemplateToUpdate"
        @submit.prevent="update"
    >
        <item-list gap="5px" class="hc-flex-1" padding="10px 0">
            <v-field :label="$t('name')" required v-slot="{ label }"
                ><input
                    :placeholder="label + ' ...'"
                    v-model="messageTemplateToUpdate.name"
                    required
            /></v-field>
            <text-editor
                style="padding: 5px"
                ref="body"
                required
                v-model.lazy="messageTemplateToUpdate.body"
                :placeholder="$t('content')"
            />
        </item-list>
        <buttons>
            <button
                v-if="can('all.project.message-template.delete')"
                @click.prevent="remove"
                class="hc-button-danger"
                v-text="$t('delete')"
            ></button>
            <button v-text="$t('update')"></button>
        </buttons>
        <loading
            :loading="updatingMessageTemplate || removingMessageTemplate"
        />
    </form>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    UPDATE_MESSAGE_TEMPLATE,
    REMOVE_MESSAGE_TEMPLATE,
} from "@/actions/project/message-template";
import { CLOSE_MODAL } from "@/actions/modal";
import { FETCH_PROSPECTS } from "@/actions/project/prospect";

export default {
    data() {
        return {
            updatingMessageTemplate: false,
            removingMessageTemplate: false,
            fetchingMessageTemplate: false,
            messageTemplateToUpdate: this.messageTemplate,
        };
    },

    created() {
        this.messageTemplateToUpdate = this.messageTemplate;
    },

    methods: {
        /**
         *
         */
        async update() {
            this.updatingMessageTemplate = true;

            try {
                await store.dispatch(
                    UPDATE_MESSAGE_TEMPLATE,
                    this.messageTemplateToUpdate
                );
                store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.updatingMessageTemplate = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingMessageTemplate = true;

                try {
                    await store.dispatch(
                        REMOVE_MESSAGE_TEMPLATE,
                        this.messageTemplateToUpdate.id
                    );
                    store.dispatch(FETCH_PROSPECTS);
                } finally {
                    this.removingMessageTemplate = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },
    },

    watch: {
        async messageTemplate(newValue) {
            if (newValue) {
                this.messageTemplateToUpdate = { ...newValue };

                /*this.fetchingMessageTemplate = true;
                this.messageTemplateToUpdate = await store.dispatch(
                    SHOW_MESSAGE_TEMPLATE,
                    newValue.id
                );
                this.fetchingMessageTemplate = false;*/
            }
        },
    },

    computed: {
        ...mapGetters(["messageTemplate", "fields", "can"]),
    },
};
</script>
