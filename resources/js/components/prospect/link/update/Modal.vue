<template>
    <modal name="prospect-link-update" :title="link ? link.name : ''">
        <form
            class="hc-flex-column"
            style="height: 100%"
            v-if="linkToUpdate"
            @submit.prevent="update"
        >
            <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                <v-field :label="$t('name')" required v-slot="p"
                    ><input
                        ref="linkName"
                        :placeholder="p.link + ' ...'"
                        v-model.lazy="linkToUpdate.name"
                        required
                /></v-field>
                <v-field label="URL" required v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model.lazy="linkToUpdate.link"
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
            <loading :loading="updatingLink || removingLink" />
        </form>
    </modal>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    SHOW_PROSPECT_LINK,
    UPDATE_PROSPECT_LINK,
    REMOVE_PROSPECT_LINK,
} from "@/actions/project/prospect/link";
import { CLOSE_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            updatingLink: false,
            removingLink: false,
            fetchingLink: false,
            linkToUpdate: this.link,
        };
    },

    methods: {
        /**
         *
         */
        async update() {
            this.updatingLink = true;

            try {
                await store.dispatch(UPDATE_PROSPECT_LINK, this.linkToUpdate);
            } finally {
                this.updatingLink = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingLink = true;

                try {
                    await store.dispatch(
                        REMOVE_PROSPECT_LINK,
                        this.linkToUpdate.id
                    );
                } finally {
                    this.removingLink = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },
    },

    watch: {
        async prospectLink(newValue) {
            if (newValue) {
                this.linkToUpdate = { ...newValue };

                /*this.fetchingLink = true;
                this.linkToUpdate = await store.dispatch(
                    SHOW_PROSPECT_LINK,
                    newValue.id
                );
                this.fetchingLink = false;*/
            }
        },
    },

    computed: {
        ...mapGetters(["prospectLink"]),
    },
};
</script>
