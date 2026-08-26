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

            <template v-if="prospectImportToUpdate.source == 'google_sheets'">
                <v-field
                    :label="$t('import.update.google_sheets.url')"
                    required
                    v-slot="{ label }"
                    ><input
                        required
                        type="url"
                        :placeholder="label + ' ...'"
                        v-model="prospectImportToUpdate.source_url"
                /></v-field>

                <v-field :label="$t('import.update.google_sheets.sync_enabled')">
                    <input
                        type="checkbox"
                        style="width: auto; min-height: 0"
                        v-model="prospectImportToUpdate.sync_enabled"
                    />
                </v-field>

                <v-field
                    v-if="prospectImportToUpdate.sync_enabled"
                    :label="$t('import.update.google_sheets.sync_interval')"
                >
                    <select
                        v-model.number="
                            prospectImportToUpdate.sync_interval_minutes
                        "
                    >
                        <option
                            :value="5"
                            v-text="
                                $t('import.add.google_sheets.sync_interval_5')
                            "
                        ></option>
                        <option
                            :value="30"
                            v-text="
                                $t('import.add.google_sheets.sync_interval_30')
                            "
                        ></option>
                        <option
                            :value="60"
                            v-text="
                                $t('import.add.google_sheets.sync_interval_60')
                            "
                        ></option>
                        <option
                            :value="180"
                            v-text="
                                $t(
                                    'import.add.google_sheets.sync_interval_180'
                                )
                            "
                        ></option>
                        <option
                            :value="1440"
                            v-text="
                                $t(
                                    'import.add.google_sheets.sync_interval_1440'
                                )
                            "
                        ></option>
                    </select>
                </v-field>

                <v-field
                    v-if="prospectImportToUpdate.sync_enabled"
                    :label="$t('import.update.google_sheets.last_synced_at')"
                >
                    <div
                        v-text="lastSyncedAtLabel"
                        style="border: none !important; min-height: auto !important; padding: 5px 0 !important"
                    ></div>
                </v-field>
            </template>
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
                const payload = {
                    id: this.prospectImportToUpdate.id,
                    name: this.prospectImportToUpdate.name,
                };

                if (this.prospectImportToUpdate.source == "google_sheets") {
                    payload.source_url = this.prospectImportToUpdate.source_url;
                    payload.sync_enabled =
                        this.prospectImportToUpdate.sync_enabled;
                    payload.sync_interval_minutes =
                        this.prospectImportToUpdate.sync_interval_minutes;
                }

                await store.dispatch(UPDATE_IMPORT, payload);
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

        /**
         * Human readable "last synced at" for the auto-sync status,
         * falling back to the "never synced yet" message.
         */
        lastSyncedAtLabel() {
            const value = this.prospectImportToUpdate.last_synced_at;

            if (!value) {
                return this.$t("import.update.google_sheets.never_synced");
            }

            return dayjs(new Date(value)).format("DD/MM/YYYY HH:mm:ss");
        },
    },
};
</script>
