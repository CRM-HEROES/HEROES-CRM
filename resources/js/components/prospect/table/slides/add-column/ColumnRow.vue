<template>
    <item @click="addOrRemove">
        <icon
            :class="column.icon"
            :style="column.iconColor ? { color: column.iconColor } : {}"
        />
        <div class="hc-item-main-content" v-text="column.name"></div>
        <slot></slot>
        <icon
            v-if="
                column.type == 'category' && can('all.project.category.update')
            "
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="editCategory"
        />
        <template v-if="existingColumn && !existingColumn.hidden">
            <icon
                :class="
                    existingColumn.fixed
                        ? 'fa fa-thumbtack icon-blue'
                        : 'fa fa-thumbtack icon-grey'
                "
                @click.prevent.stop="togglePinColumn"
            />
            <icon
                tag="a"
                :class="'fa fa-eye icon-blue'"
                @click.prevent.stop="
                    scrollToCorrespondingColumn(), closeModal()
                "
            />
            <icon
                v-if="
                    column.type == 'default-field' ||
                    column.type == 'meta-field'
                "
                :class="[
                    'fa',
                    column.align == 'right'
                        ? 'fa-align-right'
                        : column.align == 'center'
                        ? 'fa-align-center'
                        : 'fa-align-left',
                ]"
                @click.prevent.stop="toggleAlign"
            />
            <icon class="fa fa-times icon-red" />
        </template>
        <icon v-else class="fa fa-plus" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_MODAL, CLOSE_MODAL } from "@/actions/modal";
import { SET_CATEGORY } from "@/actions/project/category";

export default {
    props: {
        /**
         * Column
         */
        column: {
            type: Object,
        },

        existingColumn: {
            type: Object,
        },
    },

    methods: {
        /**
         * Add thread as column
         * to the prospects table
         * for the current user
         */
        async addOrRemove() {
            if (this.existingColumn && !this.existingColumn.hidden) {
                this.removeColumn();
            } else {
                this.addColumn();
            }
        },

        /**
         * Add column
         * to the prospects table
         * for the current user
         */
        async addColumn() {
            this.$emit("add", this.column);
        },

        /**
         * Remove column
         * from the prospects table
         * for the current user
         */
        async removeColumn() {
            this.$emit("remove", this.column);
        },

        /**
         * Pin or do not pin the column
         */
        togglePinColumn() {
            this.$emit("toggle-pin", this.column);
        },

        /**
         * Toggle align to left, center or right
         */
        toggleAlign() {
            this.$emit("toggle-align", this.column);
        },

        /**
         * When we're inside the prospects table
         * Let scroll to the column
         * corresponding to the first filter
         * from the menu
         */
        scrollToCorrespondingColumn() {
            const header = document.getElementById(this.column.headerId);

            if (!header) {
                return;
            }

            header.scrollIntoView();
        },

        /**
         *
         */
        editCategory() {
            const category = this.categories.find(
                (category) => category.id == this.column.columnId.substring(10)
            );

            if (!category) {
                return;
            }

            store.commit(OPEN_MODAL, "category-update");
            store.commit(SET_CATEGORY, category);
        },

        /**
         *
         */
        closeModal() {
            store.commit(CLOSE_MODAL);
        },
    },

    computed: {
        ...mapGetters([
            "projectUserSettingsProspectsTable",
            "projectUserSettingsProspectsTableHas",
            "categories",
            "can",
        ]),
    },
};
</script>
