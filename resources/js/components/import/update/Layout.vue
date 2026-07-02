<template>
    <form
        class="hc-flex-column"
        style="height: 100%"
        v-if="prospectImportToUpdate"
        @submit.prevent="update"
    >
        <item-list gap="5px" class="hc-flex-1" padding="10px 0">
            <v-field :label="$t('import.add.name')" required v-slot="p"
                ><input
                    ref="prospectImportName"
                    :placeholder="p.prospectImport + ' ...'"
                    v-model="prospectImportToUpdate.name"
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
        <loading :loading="updatingImport || removingImport" />
    </form>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { UPDATE_IMPORT, REMOVE_IMPORT } from "@/actions/project/import";
import { CLOSE_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            updatingImport: false,
            removingImport: false,
            fetchingImport: false,
            prospectImportToUpdate: this.prospectImport,
        };
    },

    created() {
        this.prospectImportToUpdate = this.prospectImport;
    },

    methods: {
        /**
         *
         */
        async update() {
            this.updatingImport = true;

            try {
                await store.dispatch(UPDATE_IMPORT, {
                    id: this.prospectImportToUpdate.id,
                    name: this.prospectImportToUpdate.name,
                });
            } finally {
                this.updatingImport = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingImport = true;

                try {
                    await store.dispatch(REMOVE_IMPORT, {
                        slug: this.prospectImportToUpdate.id,
                        params: {
                            import: true,
                            prospects: false,
                        },
                    });
                } finally {
                    this.removingImport = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },
    },

    watch: {
        async prospectImport(newValue) {
            if (newValue) {
                this.prospectImportToUpdate = newValue;

                /*this.fetchingImport = true;
                this.prospectImportToUpdate = await store.dispatch(
                    SHOW_IMPORT,
                    newValue.id
                );
                this.fetchingImport = false;*/
            }
        },
    },

    computed: {
        ...mapGetters(["prospectImport"]),
    },
};
</script>
