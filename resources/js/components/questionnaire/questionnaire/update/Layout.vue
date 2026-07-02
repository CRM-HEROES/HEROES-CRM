<template>
    <form
        class="hc-flex-column"
        style="height: 100%"
        v-if="questionnaireToUpdate"
        @submit.prevent="update"
    >
        <item-list gap="5px" class="hc-flex-1" padding="10px 0">
            <v-field :label="$t('name')" required v-slot="{ label }"
                ><input
                    ref="questionnaireName"
                    :placeholder="label + ' ...'"
                    v-model="questionnaireToUpdate.name"
                    required
            /></v-field>
            <!--item tag="label" style="padding-right: 5px">
                    <icon class="fa fa-user" />
                    <div class="hc-item-main-content" v-text="'Privé'"></div>
                    <checkbox v-model="questionnaire.private" />
                </item-->
        </item-list>
        <buttons>
            <button
                @click.prevent="remove"
                class="hc-button-danger"
                v-text="$t('delete')"
            ></button>
            <button v-text="$t('update')"></button>
        </buttons>
        <loading :loading="updatingQuestionnaire || removingQuestionnaire" />
    </form>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    UPDATE_QUESTIONNAIRE,
    REMOVE_QUESTIONNAIRE,
} from "@/actions/project/questionnaire";
import { CLOSE_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            updatingQuestionnaire: false,
            removingQuestionnaire: false,
            fetchingQuestionnaire: false,
            questionnaireToUpdate: this.questionnaire,
        };
    },

    created() {
        this.questionnaireToUpdate = this.questionnaire;
    },

    methods: {
        /**
         *
         */
        async update() {
            this.updatingQuestionnaire = true;

            try {
                await store.dispatch(
                    UPDATE_QUESTIONNAIRE,
                    this.questionnaireToUpdate
                );
            } finally {
                this.updatingQuestionnaire = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingQuestionnaire = true;

                try {
                    await store.dispatch(
                        REMOVE_QUESTIONNAIRE,
                        this.questionnaireToUpdate.id
                    );
                } finally {
                    this.removingQuestionnaire = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },
    },

    watch: {
        async questionnaire(newValue) {
            if (newValue) {
                this.questionnaireToUpdate = newValue;

                /*this.fetchingQuestionnaire = true;
                this.questionnaireToUpdate = await store.dispatch(
                    SHOW_QUESTIONNAIRE,
                    newValue.id
                );
                this.fetchingQuestionnaire = false;*/
            }
        },
    },

    computed: {
        ...mapGetters(["questionnaire"]),
    },
};
</script>
