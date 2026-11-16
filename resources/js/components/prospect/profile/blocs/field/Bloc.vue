<template>
    <bloc icon="fa fa-user icon-blue" :name="bloc.name">
        <template #body>
            <div style="padding: 10px 15px">
                <div v-if="showCallControls" class="hc-prospect-call-controls">
                    <label>
                        <input
                            :checked="!!prospect.call_consent"
                            :disabled="savingCallControls"
                            type="checkbox"
                            @change="saveCallControl('call_consent', $event)"
                        />
                        <span>{{ $t("prospect.call_controls.consent") }}</span>
                    </label>
                    <label>
                        <input
                            :checked="!!prospect.do_not_call"
                            :disabled="savingCallControls"
                            type="checkbox"
                            @change="saveCallControl('do_not_call', $event)"
                        />
                        <span>{{ $t("prospect.call_controls.do_not_call") }}</span>
                    </label>
                    <p v-if="callControlsError" role="alert">{{ callControlsError }}</p>
                </div>
                <draggable
                    tag="div"
                    :list="this.bloc.items"
                    :disabled="!movable"
                    group="prospect-profile-bloc-field"
                    handle=".handle"
                    @end="setBlocColumn"
                >
                    <template #item="{ element, index }">
                        <field
                            :field="
                                fields.find(
                                    (f) =>
                                        (f.meta &&
                                            element == 'meta->' + f.slug) ||
                                        (!f.meta && element == f.slug)
                                )
                            "
                            :prospect="prospect"
                        />
                    </template>
                    <template #footer>
                        <buttons>
                            <button
                                v-text="'Valider'"
                                @click="validate"
                            ></button>
                        </buttons>
                    </template>
                </draggable>
                <loading :loading="updating" />
            </div>
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import ProspectService from "@/apis/project/prospect";

// Actions
import { UPDATE_PROSPECT } from "@/actions/project/prospect";
import { UPDATE_PROJECT_USER_SETTING } from "@/actions/project/user/setting";

// Components
import Bloc from "@/components/prospect/profile/blocs/Bloc.vue";
import Field from "./Field.vue";
import DefaultField from "./DefaultField.vue";
import MetaField from "./MetaField.vue";

export default {
    components: {
        Bloc,
        Field,
        DefaultField,
        MetaField,
    },

    props: {
        bloc: {
            type: Object,
        },
    },

    data() {
        return {
            movable: deviceType() == "desktop",
            updating: false,
            savingCallControls: false,
            callControlsError: "",
        };
    },

    methods: {
        setBlocColumn(e) {
            let newSettings = [...this.projectUserSettingsProspectProfile];

            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "prospect-profile",
                value: newSettings,
            });
        },

        async validate() {
            this.updating = true;

            const updates = {
                id: this.prospect.id,
                meta: {},
            };

            this.blocFields.forEach((f) => {
                if (f.meta && this.prospect.meta) {
                    updates.meta[f.slug] = this.prospect.meta[f.slug];
                } else {
                    updates[f.slug] = this.prospect[f.slug];
                }
            });

            try {
                await store.dispatch(UPDATE_PROSPECT, updates);
            } finally {
                this.updating = false;
            }
        },

        async saveCallControl(field, event) {
            if (this.savingCallControls) {
                return;
            }

            const value = event.target.checked;
            const updates = {
                call_consent: !!this.prospect.call_consent,
                do_not_call: !!this.prospect.do_not_call,
            };
            updates[field] = value;
            if (updates.do_not_call) {
                updates.call_consent = false;
            }

            this.savingCallControls = true;
            this.callControlsError = "";
            try {
                const { data } = await ProspectService.updateCallConsent(
                    this.project.slug,
                    this.prospect.id,
                    updates
                );
                store.commit(UPDATE_PROSPECT, { id: this.prospect.id, ...data });
                event.target.checked = !!data[field];
            } catch (error) {
                event.target.checked = !!this.prospect[field];
                this.callControlsError =
                    (error.response &&
                        error.response.data &&
                        error.response.data.message) ||
                    this.$t("prospect.call_controls.update_error");
            } finally {
                this.savingCallControls = false;
            }
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "fields",
            "prospect",
            "projectUserSettingsProspectProfile",
        ]),

        showCallControls() {
            for (const column of this.projectUserSettingsProspectProfile || []) {
                for (const item of column) {
                    if (item.type === "field") {
                        return item === this.bloc;
                    }
                }
            }

            return false;
        },

        blocFields() {
            if (!this.bloc.items) {
                return [];
            }

            return this.bloc.items
                .map((item) =>
                    this.fields.find(
                        (f) =>
                            (f.meta && item == "meta->" + f.slug) ||
                            (!f.meta && item == f.slug)
                    )
                )
                .filter((i) => i);
        },
    },
};
</script>

<style scoped>
.hc-prospect-call-controls {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 12px;
    font-size: 12px;
}

.hc-prospect-call-controls label {
    display: flex;
    align-items: center;
    gap: 7px;
    margin: 0;
}
</style>
