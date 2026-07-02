<template>
    <modal
        name="event-description-template-add"
        :title="$t('event_description_template.add.title')"
        @open="eventDescriptionTemplate = this.newEventDescriptionTemplate()"
    >
        <form
            class="hc-flex-column"
            style="height: 100%"
            @submit.prevent="storeEventDescriptionTemplate"
        >
            <div class="hc-flex-column">
                <v-field :label="$t('name')" required v-slot="{ label }"
                    ><input
                        ref="name"
                        :placeholder="label + ' ...'"
                        v-model="eventDescriptionTemplate.name"
                        required
                /></v-field>
                <text-editor
                    style="padding: 5px"
                    ref="body"
                    required
                    v-model.lazy="eventDescriptionTemplate.body"
                    :placeholder="$t('content')"
                />
            </div>
            <buttons>
                <button v-text="$t('add')"></button>
            </buttons>
            <loading :loading="addingEventDescriptionTemplate" />
        </form>
    </modal>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { ADD_EVENT_DESCRIPTION_TEMPLATE } from "@/actions/project/event-description-template";
import { CLOSE_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            eventDescriptionTemplate: this.newEventDescriptionTemplate(),
            addingEventDescriptionTemplate: false,

            strategies: [
                {
                    match: /(\w*)$/,
                    index: 1,
                    search: (query, callback) => {
                        query = removeStringAccent(query);

                        callback(
                            this.fields.filter(
                                (f) =>
                                    f.for == "prospect" &&
                                    removeStringAccent(f.name).indexOf(query) >=
                                        0
                            )
                        );
                    },
                    replace(field) {
                        return (
                            (field.meta
                                ? `{prospect.meta.${field.slug}}`
                                : `{prospect.${field.slug}}`) + " "
                        );
                    },
                    template(field) {
                        return field.name;
                    },
                },
            ],
        };
    },

    methods: {
        /**
         *
         */
        newEventDescriptionTemplate() {
            return {
                name: "",
                body: "",
            };
        },

        /**
         *
         */
        async storeEventDescriptionTemplate() {
            if (!this.eventDescriptionTemplate.name) {
                this.$refs.name.focus();
                return;
            }

            if (!this.eventDescriptionTemplate.body) {
                this.$refs.body.focus();
                return;
            }

            this.addingEventDescriptionTemplate = true;

            try {
                await store.dispatch(
                    ADD_EVENT_DESCRIPTION_TEMPLATE,
                    this.eventDescriptionTemplate
                );
            } finally {
                this.addingEventDescriptionTemplate = false;
                store.commit(CLOSE_MODAL);
            }
        },
    },

    computed: {
        ...mapGetters(["fields"]),
    },
};
</script>
