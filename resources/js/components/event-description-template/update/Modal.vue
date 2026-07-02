<template>
    <modal
        name="event-description-template-update"
        :title="eventDescriptionTemplate ? eventDescriptionTemplate.name : ''"
    >
        <form
            class="hc-flex-column"
            style="height: 100%"
            v-if="eventDescriptionTemplateToUpdate"
            @submit.prevent="update"
        >
            <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                <v-field :label="$t('name')" required v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="eventDescriptionTemplateToUpdate.name"
                        required
                /></v-field>
                <text-editor
                    style="padding: 5px"
                    ref="body"
                    required
                    v-model.lazy="eventDescriptionTemplateToUpdate.body"
                    :placeholder="$t('content')"
                />
            </item-list>
            <buttons>
                <button
                    v-if="can('all.project.event-description-template.delete')"
                    @click.prevent="remove"
                    class="hc-button-danger"
                    v-text="$t('delete')"
                ></button>
                <button v-text="$t('update')"></button>
            </buttons>
            <loading
                :loading="
                    updatingEventDescriptionTemplate ||
                    removingEventDescriptionTemplate
                "
            />
        </form>
    </modal>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    SHOW_EVENT_DESCRIPTION_TEMPLATE,
    UPDATE_EVENT_DESCRIPTION_TEMPLATE,
    REMOVE_EVENT_DESCRIPTION_TEMPLATE,
} from "@/actions/project/event-description-template";
import { CLOSE_MODAL } from "@/actions/modal";
import { FETCH_PROSPECTS } from "@/actions/project/prospect";

export default {
    data() {
        return {
            updatingEventDescriptionTemplate: false,
            removingEventDescriptionTemplate: false,
            fetchingEventDescriptionTemplate: false,
            eventDescriptionTemplateToUpdate: this.eventDescriptionTemplate,
        };
    },

    methods: {
        /**
         *
         */
        async update() {
            this.updatingEventDescriptionTemplate = true;

            try {
                await store.dispatch(
                    UPDATE_EVENT_DESCRIPTION_TEMPLATE,
                    this.eventDescriptionTemplateToUpdate
                );
                store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.updatingEventDescriptionTemplate = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingEventDescriptionTemplate = true;

                try {
                    await store.dispatch(
                        REMOVE_EVENT_DESCRIPTION_TEMPLATE,
                        this.eventDescriptionTemplateToUpdate.id
                    );
                    store.dispatch(FETCH_PROSPECTS);
                } finally {
                    this.removingEventDescriptionTemplate = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },
    },

    watch: {
        async eventDescriptionTemplate(newValue) {
            if (newValue) {
                this.eventDescriptionTemplateToUpdate = { ...newValue };

                /*this.fetchingEventDescriptionTemplate = true;
                this.eventDescriptionTemplateToUpdate = await store.dispatch(
                    SHOW_EVENT_DESCRIPTION_TEMPLATE,
                    newValue.id
                );
                this.fetchingEventDescriptionTemplate = false;*/
            }
        },
    },

    computed: {
        ...mapGetters(["eventDescriptionTemplate", "fields", "can"]),
    },
};
</script>
