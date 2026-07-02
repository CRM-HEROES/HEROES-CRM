<template>
    <autocomplete
        ref="textarea"
        v-model="message"
        :placeholder="$t('content') + ' ...'"
        :auto-height="true"
        :strategies="strategies"
        @click.stop
        @blur=""
        @keydown.enter=""
        @selected=""
    />
</template>

<script>
import { mapGetters } from "vuex";

export default {
    data() {
        return {
            strategies: [
                {
                    match: /(?:\{prospect.)?(\w+)$/,
                    index: 1,
                    search: (query, callback) => {
                        query = removeStringAccent(query);

                        callback(
                            this.fields
                                .filter(
                                    (f) =>
                                        removeStringAccent(f.name).indexOf(
                                            query
                                        ) >= 0 ||
                                        removeStringAccent(f.slug).indexOf(
                                            query
                                        ) >= 0
                                )
                                .map((f) => ({ ...f, acType: "html" }))
                        );
                    },
                    replace(field) {
                        return `{${field.for}.${field.slug}} `;
                    },
                    template(field) {
                        switch (field.for) {
                            case "prospect":
                                return `Prospect: ${field.name}`;
                            case "user":
                                return `Utilisateur: ${field.name}`;
                            case "project":
                                return `Projet: ${field.name}`;
                            case "product":
                                return `Produit: ${field.name}`;
                            case "order":
                                return `Devis: ${field.name}`;
                            default:
                                return `${field.name}`;
                        }
                    },
                },
                /*
                {
                    match: /(?:\{prospect.)?(\w+)$/,
                    index: 1,
                    search: (query, callback) => {
                        query = removeStringAccent(query);

                        callback(
                            [
                                { name: "MAP Plan", type: "plan", zoom: 18 },
                                {
                                    name: "MAP Satellite",
                                    type: "satellite",
                                    zoom: 18,
                                },
                            ]
                                .filter(
                                    (f) =>
                                        removeStringAccent(f.name).indexOf(
                                            query
                                        ) >= 0
                                )
                                .map((f) => ({ ...f, acType: "map" }))
                        );
                    },
                    replace(map) {
                        const { type, zoom } = map;
                        return JSON.stringify({ type, zoom });
                    },
                    template(map) {
                        return map.name;
                    },
                },
                */
            ],
        };
    },

    methods: {
        focus() {
            this.$refs.textarea.focus();
        },
    },

    computed: {
        ...mapGetters(["project", "fields"]),
    },
};
</script>
