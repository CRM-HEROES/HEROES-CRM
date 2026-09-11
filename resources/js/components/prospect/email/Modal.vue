<template>
    <modal :name="name" title="Envoyer un email" @open="loadDraft">
        <form class="hc-flex-column" style="height: 100%" @submit.prevent="send">
            <item-list gap="8px" class="hc-flex-1" padding="10px 0">
                <v-field label="Destinataire" required v-slot="{ label }">
                    <input v-model="draft.to" :placeholder="label + ' ...'" type="email" :required="!draft.prospects" />
                </v-field>
                <v-field label="Objet" required v-slot="{ label }">
                    <input v-model="draft.subject" :placeholder="label + ' ...'" required />
                </v-field>
                <v-field label="Message" required v-slot="{ label }">
                    <textarea v-model="draft.body" :placeholder="label + ' ...'" rows="9" required></textarea>
                </v-field>
            </item-list>
            <buttons>
                <button v-text="$t('send')"></button>
            </buttons>
            <loading :loading="sending" />
        </form>
    </modal>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import ProspectEmailService from "@/apis/project/prospect/email";
import { CLOSE_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            name: "prospect-email",
            sending: false,
            draft: this.emptyDraft(),
        };
    },

    computed: {
        ...mapGetters(["project", "prospectEmailDraft"]),
    },

    methods: {
        emptyDraft() {
            return {
                prospect: null,
                to: "",
                category: "Message",
                subject: "",
                body: "",
            };
        },

        loadDraft() {
            this.draft = this.prospectEmailDraft
                ? { ...this.prospectEmailDraft }
                : this.emptyDraft();
        },

        async send() {
            this.sending = true;

            try {
                if (this.draft.prospects) {
                    await ProspectEmailService.sendBulk(this.project.slug, this.draft);
                } else {
                    await ProspectEmailService.send(this.project.slug, this.draft.prospect, this.draft);
                }
                store.commit("CLEAR_PROSPECT_EMAIL_DRAFT");
                store.commit(CLOSE_MODAL);
            } finally {
                this.sending = false;
            }
        },
    },
};
</script>