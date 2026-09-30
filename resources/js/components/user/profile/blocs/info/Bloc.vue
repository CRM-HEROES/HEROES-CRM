<template>
    <bloc icon="fa fa-user" :name="bloc.name">
        <template #body>
            <div style="padding: 10px 15px">
                <template v-for="field in availableFields" :key="field.id">
                    <meta-field v-if="field.meta" :field="field" :user="user" />
                    <default-field v-else :field="field" :user="user" />
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
import Bloc from "@/components/user/profile/blocs/Bloc.vue";
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
        ...mapGetters(["fields", "user"]),

        availableFields() {
            return [
                {
                    slug: "name",
                    name: "Nom",
                    meta: false,
                },
                {
                    slug: "last_name",
                    name: "Prénom",
                    meta: false,
                },
                {
                    slug: "email",
                    name: "Email",
                    meta: false,
                },
                {
                    slug: "password",
                    name: "Mot de passe",
                    meta: false,
                },
                {
                    slug: "street",
                    name: "Rue",
                    meta: false,
                },
                {
                    slug: "street_bis",
                    name: "Rue 2",
                    meta: false,
                },
                {
                    slug: "postal_code",
                    name: "CP",
                    meta: false,
                },
                {
                    slug: "city",
                    name: "Ville",
                    meta: false,
                },
                {
                    slug: "country",
                    name: "Pays",
                    meta: false,
                },
            ].filter((field) => this.bloc.items.indexOf(field.slug) >= 0);
        },
    },
};
</script>
