<template>
    <bloc icon="fa fa-user" :name="bloc.name">
        <template #body>
            <div style="padding: 10px 15px">
                <template v-for="field in availableFields" :key="field.id">
                    <meta-field
                        v-if="field.meta"
                        :field="field"
                        :project="project"
                    />
                    <default-field v-else :field="field" :project="project" />
                </template>
            </div>
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";

// Actions

// Components
import Bloc from "@/components/project/profile/blocs/Bloc.vue";
import DefaultField from "./DefaultField.vue";
import MetaField from "./MetaField.vue";

export default {
    components: {
        Bloc,
        DefaultField,
        MetaField,
    },

    props: {
        bloc: {
            type: Object,
        },
    },

    computed: {
        ...mapGetters(["project", "fields", "project"]),

        availableFields() {
            return this.fields.filter(
                (f) =>
                    f.for == "project" &&
                    ((f.meta &&
                        this.bloc.items.indexOf("meta->" + f.slug) >= 0) ||
                        (!f.meta && this.bloc.items.indexOf(f.slug) >= 0))
            );
        },
    },
};
</script>
