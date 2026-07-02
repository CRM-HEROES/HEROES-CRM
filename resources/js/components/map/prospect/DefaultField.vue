<template>
    <label class="hc-map-prospect-field">
        <div class="hc-map-prospect-field-label" v-text="field.name"></div>

        <div class="hc-map-prospect-field-input">
            <street-cell
                v-if="field.slug == 'street'"
                :prospect="prospect"
                :field="field.slug"
            />
            <default-cell v-else :prospect="prospect" :field="field.slug" />
        </div>
    </label>
</template>

<style>
.hc-map-prospect-field {
    width: 100%;
    min-height: 26px;
    display: flex;
    flex-direction: row;
    font-size: 12px;
}

.hc-map-prospect-field-label {
    padding: 0 10px;
    min-width: 60px;
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    line-height: 26px;
    color: #555;
}

.hc-map-prospect-field-input {
    flex: 1;
    position: relative;
    outline: 1px solid #eee;
}

.hc-map-prospect-field-input input {
    height: 26px;
}

.hc-map-prospect-field-input .hc-default-cell-label {
    height: 26px;
}

.hc-map-prospect-field-input .hc-default-cell-label > span {
    line-height: 26px;
}
</style>

<script>
import DefaultCell from "@/components/prospect/table/cell/DefaultCell.vue";
import StreetCell from "@/components/prospect/table/cell/StreetCell.vue";

export default {
    components: {
        DefaultCell,
        StreetCell,
    },

    props: {
        /**
         * Field
         */
        field: {
            type: Object,
        },

        /**
         */
        prospect: {
            type: Object,
        },
    },
};
</script>
