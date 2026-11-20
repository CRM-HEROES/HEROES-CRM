<template>
    <div class="hc-relation-cell">
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

<script>
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
};
</script>
