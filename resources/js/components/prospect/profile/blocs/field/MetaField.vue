<template>
    <label class="hc-prospect-profile-prospect-field">
        <div
            class="hc-prospect-profile-prospect-field-label handle"
            v-text="field.name"
        ></div>

        <div class="hc-prospect-profile-prospect-field-input">
            <meta-cell
                :prospect="prospect"
                :field="field.slug"
                :type="field.type"
                :placeholder="field.name"
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
