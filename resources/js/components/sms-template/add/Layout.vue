<template>
    <form
        class="hc-flex-column"
        style="height: 100%"
        @submit.prevent="storeSmsTemplate"
    >
        <div class="hc-flex-column">
            <v-field label="Nom" required v-slot="{ label }"
                ><input
                    :placeholder="label + ' ...'"
                    v-model="smsTemplate.name"
                    required
            /></v-field>
            <v-field label="Contenu" required v-slot="{ label }"
                ><autocomplete
                    :placeholder="label + ' ...'"
                    :strategies="strategies"
                    v-model="smsTemplate.body"
                    style="height: auto"
                    required
            /></v-field>
        </div>
        <buttons>
            <button v-text="$t('add')"></button>
        </buttons>
        <loading :loading="addingSmsTemplate" />
    </form>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { ADD_SMS_TEMPLATE } from "@/actions/project/sms-template";
import { CLOSE_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            smsTemplate: this.newSmsTemplate(),
            addingSmsTemplate: false,

            strategies: [
                {
                    match: /\{prospect.(\w*)$/,
                    index: 1,
                    search: (query, callback) => {
                        query = removeStringAccent(query);

                        callback(
                            this.fields.filter(
                                (f) =>
                                    f.for == "prospect" &&
                                    !f.meta &&
                                    removeStringAccent(f.slug).indexOf(query) >=
                                        0
                            )
                        );
                    },
                    replace(field) {
                        return `{prospect.${field.slug}} `;
                    },
                    template(field) {
                        return field.name + " (Prospect)";
                    },
                },
                {
                    match: /\{prospect.meta.(\w*)$/,
                    index: 1,
                    search: (query, callback) => {
                        query = removeStringAccent(query);

                        callback(
                            this.fields.filter(
                                (f) =>
                                    f.for == "prospect" &&
                                    f.meta &&
                                    removeStringAccent(f.slug).indexOf(query) >=
                                        0
                            )
                        );
                    },
                    replace(field) {
                        return `{prospect.meta.${field.slug}} `;
                    },
                    template(field) {
                        return field.name + " (Prospect)";
                    },
                },
                {
                    match: /(\w+)$/,
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
                        return field.name + " (Prospect)";
                    },
                },
            ],
        };
    },

    methods: {
        /**
         *
         */
        newSmsTemplate() {
            return {
                name: "",
                body: "",
            };
        },

        /**
         *
         */
        async storeSmsTemplate() {
            this.addingSmsTemplate = true;

            try {
                await store.dispatch(ADD_SMS_TEMPLATE, this.smsTemplate);
            } finally {
                this.addingSmsTemplate = false;
                this.smsTemplate = this.newSmsTemplate();
                store.commit(CLOSE_MODAL);
            }
        },
    },

    computed: {
        ...mapGetters(["fields"]),
    },
};
</script>
