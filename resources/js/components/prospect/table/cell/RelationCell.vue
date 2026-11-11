<template>
    <div class="hc-relation-cell">
        <i
            v-if="isRevisionLoading"
            class="fa fa-spinner fa-spin hc-relation-cell-loading"
            aria-hidden="true"
        />
        <tag
            v-for="item in items"
            :key="item.id"
            :text="item[name]"
            :color="item[color] ? item[color] : '#333333'"
            :bgcolor="item[bgcolor] ? item[bgcolor] : '#EEEEEE'"
            @click="onItemClicked($event, item)"
        />
    </div>
</template>

<style>
.hc-relation-cell-loading {
    display: inline-block;
    font-size: 11px;
    margin: 0 4px;
    vertical-align: middle;
}
</style>

<script>
import { mapGetters } from "vuex";

export default {
    props: {
        /**
         * Prospect
         */
        prospect: {
            type: Object,
        },

        /**
         * Prospect asscoaited items
         * (labels, groups, users, ...)
         */
        items: {
            type: Array,
        },

        /**
         * Name field of each item
         */
        name: {
            type: String,
            default: "name",
        },

        /**
         * Color field of each item
         */
        color: {
            type: String,
            default: "color",
        },

        /**
         * Bg color field of each item
         */
        bgcolor: {
            type: String,
            default: "bgcolor",
        },

        categoryId: {
            type: [Number, String],
            default: null,
        },

        /**
         * Item clicked
         */
        itemClicked: {
            type: Function,
            default: null,
        },
    },

    methods: {
        onItemClicked(event, item) {
            if (this.itemClicked) {
                event.stopPropagation();
                this.itemClicked(item);
            }
        },
    },

    computed: {
        ...mapGetters(["project", "isProspectRevisionLoading"]),

        isRevisionLoading() {
            return (
                this.categoryId !== null &&
                this.isProspectRevisionLoading(
                    this.project.slug,
                    this.prospect.id,
                    this.categoryId
                )
            );
        },
    },
};
</script>
