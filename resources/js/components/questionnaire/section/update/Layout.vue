<template>
    <form
        class="hc-flex-column"
        style="height: 100%"
        v-if="sectionToUpdate"
        @submit.prevent="update"
    >
        <item-list gap="5px" class="hc-flex-1" padding="10px 0">
            <v-field :label="$t('name')" required v-slot="{ label }"
                ><input
                    ref="sectionName"
                    :placeholder="label + ' ...'"
                    v-model="sectionToUpdate.name"
                    required
            /></v-field>
        </item-list>
        <buttons>
            <button
                @click.prevent="remove"
                class="hc-button-danger"
                v-text="$t('delete')"
            ></button>
            <button v-text="$t('update')"></button>
        </buttons>
        <loading :loading="updatingSection || removingSection" />
    </form>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    UPDATE_QUESTIONNAIRE_SECTION,
    REMOVE_QUESTIONNAIRE_SECTION,
} from "@/actions/project/questionnaire/section";
import { CLOSE_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            updatingSection: false,
            removingSection: false,
            fetchingSection: false,
            sectionToUpdate: this.questionnaireSection,
        };
    },

    created() {
        this.sectionToUpdate = this.questionnaireSection;
    },

    methods: {
        /**
         *
         */
        async update() {
            this.updatingSection = true;

            try {
                await store.dispatch(UPDATE_QUESTIONNAIRE_SECTION, {
                    questionnaire: this.questionnaire.id,
                    params: this.sectionToUpdate,
                });
            } finally {
                this.updatingSection = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingSection = true;

                try {
                    await store.dispatch(REMOVE_QUESTIONNAIRE_SECTION, {
                        questionnaire: this.questionnaire.id,
                        params: this.sectionToUpdate.id,
                    });
                } finally {
                    this.removingSection = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },
    },

    watch: {
        async questionnaireSection(newValue) {
            if (newValue) {
                this.sectionToUpdate = newValue;

                /*this.fetchingSection = true;
                this.sectionToUpdate = await store.dispatch(
                    SHOW_QUESTIONNAIRE_SECTION,
                    newValue.id
                );
                this.fetchingSection = false;*/
            }
        },
    },

    computed: {
        ...mapGetters(["questionnaire", "questionnaireSection"]),
    },
};
</script>
