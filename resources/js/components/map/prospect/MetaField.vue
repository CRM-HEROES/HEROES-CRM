<template>
    <label class="hc-map-prospect-field">
        <div class="hc-map-prospect-field-label" v-text="field.name"></div>

        <div class="hc-map-prospect-field-input">
            <meta-cell
                :prospect="prospect"
                :field="field.slug"
                :type="field.type"
            />
        </div>
    </label>
</template>

<script>
import MetaCell from "@/components/prospect/table/cell/MetaCell.vue";

export default {
    components: {
        MetaCell,
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
