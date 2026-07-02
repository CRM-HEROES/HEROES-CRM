<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="filterKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item
                v-for="filter in filteredFilters"
                @click.prevent="openFilter(filter)"
            >
                <icon :class="filter.icon" />
                <div class="hc-item-main-content" v-text="filter.name"></div>
                <icon
                    tag="a"
                    v-if="isFiltered(filter)"
                    class="fa fa-circle icon-purple"
                    :icon-size="8"
                    @click.prevent.stop="removeFilter(filter)"
                />
                <icon class="fa fa-caret-right" />
            </item>
        </item-list>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_SUB_SLIDE } from "@/actions/slide";

// Actions
import {
    REMOVE_PROSPECT_PARAMS,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";

export default {
    data() {
        return {
            filterKeyword: "",
        };
    },

    methods: {
        openFilter(filter) {
            store.commit(OPEN_SUB_SLIDE, filter.slide);
        },

        isFiltered(filter) {
            for (let i in filter.filters) {
                if (this.prospectsParamExists(filter.filters[i])) {
                    return true;
                }
            }

            return false;
        },

        removeFilter(filter) {
            for (let i in filter.filters) {
                store.commit(REMOVE_PROSPECT_PARAMS, {
                    key: filter.filters[i],
                });
            }
            store.dispatch(FETCH_PROSPECTS);
        },

        filters() {
            return [
                // Event
                {
                    filters: ["withEvents", "withoutEvents"],
                    name: "RDV",
                    icon: "fa fa-calendar icon-purple",
                    slide: "prospects-table-filter-event",
                },
                // Field
                {
                    filters: this.fields
                        .map((f) => "field_" + f.slug)
                        .concat(this.fields.map((f) => "withField_" + f.slug)),
                    name: "Champs",
                    icon: "fa fa-info icon-blue",
                    slide: "prospects-table-filter-field",
                },
                // File
                {
                    filters: ["withFiles", "withoutFiles"],
                    name: "Fichier",
                    icon: "fa fa-folder icon-blue",
                    slide: "prospects-table-filter-file",
                },
                // Group
                {
                    filters: ["withGroups", "withoutGroups"],
                    name: "Groupe",
                    icon: "fa fa-users icon-brown",
                    slide: "prospects-table-filter-group",
                },
                // Import
                {
                    filters: ["withImports", "withoutImports"],
                    name: "Import",
                    icon: "fa fa-file-download icon-brown",
                    slide: "prospects-table-filter-import",
                },
                // Interaction
                {
                    filters: ["withInteractions", "withoutInteractions"],
                    name: "Appel",
                    icon: "fa fa-phone icon-orange",
                    slide: "prospects-table-filter-interaction",
                },
                // Label
                {
                    filters: this.categories
                        .map((category) => "withCategory_" + category.id)
                        .concat(
                            this.categories.map(
                                (category) => "withoutCategory_" + category.id
                            )
                        ),
                    name: "Filtre",
                    icon: "fa fa-tags icon-garnet",
                    slide: "prospects-table-filter-label",
                },
                // Message
                {
                    filters: ["withMessages", "withoutMessages"],
                    name: "Message",
                    icon: "fa fa-envelope icon-green",
                    slide: "prospects-table-filter-message",
                },
                // Order
                {
                    filters: ["withOrders", "withoutOrders"],
                    name: "Devis",
                    icon: "fa fa-shopping-cart icon-cyan",
                    slide: "prospects-table-filter-order",
                },
                // SMS
                {
                    filters: ["withSms", "withoutSms"],
                    name: "SMS",
                    icon: "fa fa-comments icon-purple",
                    slide: "prospects-table-filter-sms",
                },
                // User
                {
                    filters: ["withUsers", "withoutUsers"],
                    name: "Utilisateurs affectés",
                    icon: "fa fa-user icon-brown",
                    slide: "prospects-table-filter-user",
                },
                //Threads
                {
                    filters: ["withUsers"],
                    name: "Utilisateurs affectés",
                    icon: "fa fa-user icon-brown",
                    slide: "prospects-table-filter-thread",
                },
            ];
        },
    },

    computed: {
        ...mapGetters(["prospectsParamExists", "categories", "fields"]),

        filteredFilters() {
            const keyword = removeStringAccent(this.filterKeyword);

            console.log(this.filters().filter(
                (filter) =>
                    removeStringAccent(filter.name).indexOf(keyword) ))

            return this.filters().filter(
                (filter) =>
                    removeStringAccent(filter.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
