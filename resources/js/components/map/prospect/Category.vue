<template>
    <div class="hc-map-prospect-labels">
        <div class="hc-map-prospect-category">
            <i class="fa fa-tags" :style="style"></i>
            <span v-text="category.name"></span>
        </div>
        <prospect-label
            v-for="label in labels"
            :key="label.id"
            :label="label"
            :prospect="prospect"
        />
    </div>
</template>

<style>
.hc-map-prospect-labels {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    padding: 3px;
    border-radius: 3px;
    cursor: pointer;
}

.hc-map-prospect-labels:hover {
    background-color: #eee;
}

.hc-map-prospect-category {
    font-size: 11px;
    height: 20px;
    line-height: 20px;
    padding: 0 5px;
    border-radius: 2px;
    display: inline-block;
    margin: 1px;
    white-space: nowrap;
}

.hc-map-prospect-category > i {
    margin-right: 7px;
}
</style>

<script>
import ProspectLabel from "./Label.vue";

export default {
    components: {
        ProspectLabel,
    },

    props: {
        /**
         * Label
         */
        category: {
            type: Object,
        },

        /**
         */
        prospect: {
            type: Object,
        },
    },

    computed: {
        /**
         *
         */
        labels() {
            return this.prospect.labels.filter(
                (label) => label.category_id == this.category.id
            );
        },

        /**
         *
         */
        style() {
            return {
                color: this.category.bgcolor,
            };
        },
    },
};
</script>
