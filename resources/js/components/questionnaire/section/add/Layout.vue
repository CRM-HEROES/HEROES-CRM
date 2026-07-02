<template>
    <form
        class="hc-flex-column"
        style="height: 100%"
        @submit.prevent="storeSection"
    >
        <item-list gap="5px">
            <v-field :label="$t('name')" required v-slot="{ label }"
                ><input
                    :placeholder="label + ' ...'"
                    v-model="section.name"
                    required
            /></v-field>
        </item-list>
        <buttons>
            <button v-text="$t('add')"></button>
        </buttons>
        <loading :loading="addingSection" />
    </form>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { ADD_QUESTIONNAIRE_SECTION } from "@/actions/project/questionnaire/section";
import { CLOSE_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            section: this.newSection(),
            addingSection: false,
        };
    },

    methods: {
        /**
         *
         */
        newSection() {
            return {
                name: "",
            };
        },

        /**
         *
         */
        async storeSection() {
            this.addingSection = true;

            try {
                await store.dispatch(ADD_QUESTIONNAIRE_SECTION, {
                    questionnaire: this.questionnaire.id,
                    params: this.section,
                });
            } finally {
                this.addingSection = false;
                this.section = this.newSection();
                store.commit(CLOSE_MODAL);
            }
        },
    },

    computed: {
        ...mapGetters(["questionnaire"]),
    },
};
</script>
