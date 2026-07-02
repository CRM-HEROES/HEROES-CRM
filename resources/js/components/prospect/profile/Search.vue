<template>
    <div
        id="hc-prospect-search"
        :class="inputFocused ? 'focused' : ''"
        @click="focusInput"
    >
        <div id="hc-prospect-search-input-container">
            <input
                ref="input"
                id="hc-prospect-search-input"
                :placeholder="$t('search') + ' ...'"
                v-model="keyword"
                @focus="open = true"
                @blur="close"
                @keydown="selectAutocomplete"
            />
        </div>
        <icon
            class="fa fa-search"
            id="hc-prospect-search-input-icon"
            :size="34"
            :icon-size="12"
            color="#AAAAAA"
        >
            <loading :loading="loadingProspect || loadingUser"
        /></icon>
        <item-list
            id="hc-prospect-search-result"
            v-if="hasResults"
            @click.stop="open = false"
        >
            <search-row
                v-for="(item, i) in filteredItems"
                :key="item.type + '-' + item.item.id"
                :id="
                    'hc-prospect-search-result-' +
                    item.type +
                    '-' +
                    item.item.id
                "
                :selected="selected == i"
                :item="item.item"
                :type="item.type"
            />
        </item-list>
    </div>
</template>

<style>
#hc-prospect-search {
    height: 34px;
    width: 240px;
    position: relative;
}

#hc-prospect-search-input-container {
    height: 100%;
    width: 100%;
    position: relative;
}

#hc-prospect-search-input-icon {
    position: absolute;
    right: 0;
    top: 0;
}

#hc-prospect-search-input {
    height: 100%;
    width: 100%;
    border-radius: 7px;
    border: none;
    padding: 0 10px;
    font-size: 12px;
    background-color: #ebebeb;
}

#hc-prospect-search-input:focus {
    outline: 2px solid #1e88e5;
}

#hc-prospect-search-result {
    position: absolute;
    left: 0;
    top: 36px;
    padding: 5px;
    width: 100%;
    height: auto;
    min-height: 38px;
    max-height: 290px;
    overflow: auto;
    z-index: 4;
    border-radius: 3px;
    background: #fff;
    box-shadow: 0px 9px 33px 0px rgba(0, 0, 0, 0.07),
        0px 0px 2px 0px rgba(0, 0, 0, 0.25);
}

#hc-prospect-search-result > .selected {
    background-color: #12a0f3;
}

#hc-prospect-search-result > .selected * {
    color: white !important;
}

@media (max-width: 767px) {
    #hc-prospect-search {
        width: 100%;
    }
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import SearchRow from "./search/SearchRow.vue";

export default {
    components: {
        SearchRow,
    },

    data() {
        return {
            keyword: "",
            open: false,
            actions: [],
        };
    },

    methods: {
        /**
         *
         */
        close() {
            setTimeout(() => {
                this.open = false;
            }, 250);
            this.inputFocused = false;
        },

        /**
         *
         * @param {*} e
         */
        selectAutocomplete(e) {
            if (this.filteredItems.length > 0) {
                // Down
                if (e.keyCode == 40) {
                    e.preventDefault();

                    if (this.selected + 1 < this.filteredItems.length) {
                        this.selected++;
                    } else {
                        this.selected = -1;
                    }

                    return;
                    // Up
                } else if (e.keyCode == 38) {
                    e.preventDefault();

                    if (this.selected <= -1) {
                        this.selected = this.filteredItems.length - 1;
                    } else {
                        this.selected--;
                    }

                    return;
                    // Enter
                } else if (e.keyCode == 13) {
                    if (this.keyword.length == 0) {
                        store.commit(INIT_PROSPECT_PARAMS);
                        store.dispatch(FETCH_PROSPECTS);
                        return;
                    }

                    e.preventDefault();

                    if (this.selected >= 0) {
                        const item = this.filteredItems[this.selected];
                        document
                            .getElementById(
                                "hc-prospect-search-result-" +
                                    item.type +
                                    "-" +
                                    item.item.id
                            )
                            .click();
                    } else if (this.$route.name == "prospect") {
                        store.commit(SET_PROSPECT_PARAMS, {
                            query: this.keyword,
                        });
                        store.dispatch(FETCH_PROSPECTS);
                    } else {
                        this.$router.push({
                            name: "prospect",
                            params: {
                                project: this.project.slug,
                            },
                            query: {
                                filters: JSON.stringify({
                                    query: this.keyword,
                                }),
                            },
                        });
                    }

                    document.getElementById("hc-prospect-search-input").blur();
                    // this.open = true;

                    if (this.prospectCancelTokenSource) {
                        this.prospectCancelTokenSource.cancel();
                    }

                    return;
                }
            }
        },

        /**
         * Scroll to selected item
         */
        scrollToSelectedItem() {
            if (this.filteredItems.length == 0) {
                return;
            }

            const item = this.filteredItems[this.selected];
            const selectedItem = document.getElementById(
                "hc-prospect-search-result-" + item.type + "-" + item.item.id
            );
            const result = document.getElementById("hc-prospect-search-result");

            if (result.scrollTop > selectedItem.offsetTop) {
                result.scrollTop = selectedItem.offsetTop - 5;
            } else if (
                selectedItem.offsetTop >
                result.scrollTop +
                    result.offsetHeight -
                    selectedItem.offsetHeight -
                    5
            ) {
                result.scrollTop =
                    selectedItem.offsetTop -
                    result.offsetHeight +
                    selectedItem.offsetHeight +
                    5;
            }
        },

        /**
         *
         */
        focusInput() {
            setTimeout(() => {
                this.$refs.input.focus();
            }, 100);
            this.inputFocused = true;
        },

        /**
         *
         */
        filteredActions(keyword) {
            return this.actions.filter(
                (action) =>
                    removeStringAccent(action.keywords).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredEvents(keyword) {
            return this.prospectEvents.filter(
                (event) =>
                    removeStringAccent(event.calendar.name).indexOf(keyword) >=
                    0
            );
        },

        /**
         *
         */
        filteredFields(keyword) {
            return this.fields.filter(
                (field) =>
                    field.for == "prospect" &&
                    (removeStringAccent(field.name).indexOf(keyword) >= 0 ||
                        removeStringAccent(
                            field.meta
                                ? this.prospect.meta[field.slug]
                                : this.prospect[field.slug]
                        ).indexOf(keyword) >= 0)
            );
        },

        /**
         *
         */
        filteredFiles(keyword) {
            return this.prospectFiles.filter(
                (file) => removeStringAccent(file.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredLabels(keyword) {
            return this.labels.filter(
                (label) =>
                    removeStringAccent(label.name).indexOf(keyword) >= 0 ||
                    removeStringAccent(label.category.name).indexOf(keyword) >=
                        0
            );
        },

        /**
         *
         */
        filteredLinks(keyword) {
            return this.prospectLinks.filter(
                (link) => removeStringAccent(link.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredOrders(keyword) {
            return this.prospectOrders.filter(
                (order) => removeStringAccent(order.name).indexOf(keyword) >= 0
            );
        },
    },

    watch: {
        keyword() {
            this.selected = -1;
        },

        async selected() {
            await this.$nextTick();
            if (this.hasResults) {
                this.scrollToSelectedItem();
            }
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "prospect",
            "fields",
            "categories",
            "prospectEvents",
            "prospectFiles",
            "prospectLabels",
            "prospectLinks",
            "prospectOrders",
        ]),

        /**
         *
         */
        hasResults() {
            return (
                this.open &&
                this.keyword.length > 0 &&
                this.filteredItems.length > 0
            );
        },

        /**
         *
         */
        labels() {
            return this.prospectLabels
                .map((label) => {
                    const category = this.categories.find(
                        (category) => category.id == label.category_id
                    );

                    return {
                        ...label,
                        category,
                    };
                })
                .filter((label) => label.category);
        },

        filteredItems() {
            const keyword = removeStringAccent(this.keyword);

            const search = {
                event: this.filteredEvents,
                field: this.filteredFields,
                file: this.filteredFiles,
                label: this.filteredLabels,
                link: this.filteredLinks,
                order: this.filteredOrders,
            };

            const result = Object.entries(search)
                .map(([type, fn]) =>
                    fn(keyword).map((item) => ({
                        type,
                        item,
                    }))
                )
                .reduce((carry, items) => [...items, ...carry], []);

            return result;
        },
    },
};
</script>
