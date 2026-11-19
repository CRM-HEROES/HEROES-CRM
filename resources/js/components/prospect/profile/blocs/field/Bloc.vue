<template>
    <bloc icon="fa fa-user icon-blue" :name="bloc.name">
        <template #body>
            <div style="padding: 10px 15px">
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
                        <buttons v-if="blocFields.length > 0">
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
    },

    computed: {
        ...mapGetters([
            "project",
            "fields",
            "prospect",
            "projectUserSettingsProspectProfile",
        ]),

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
