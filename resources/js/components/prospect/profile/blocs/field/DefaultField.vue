<template>
    <label class="hc-prospect-profile-prospect-field">
        <div
            class="hc-prospect-profile-prospect-field-label handle"
            v-text="field.name"
        ></div>

        <div class="hc-prospect-profile-prospect-field-input">
            <street-cell
                v-if="field.slug == 'street'"
                :prospect="prospect"
                :field="field.slug"
                :placeholder="field.name"
            />
            <default-cell
                v-else
                :prospect="prospect"
                :field="field.slug"
                :placeholder="field.name"
            />
        </div>
    </label>
</template>

<style>
.hc-prospect-profile-prospect-field {
    width: 100%;
    min-height: 30px;
    display: flex;
    flex-direction: row;
    font-size: 12px;
}

.hc-prospect-profile-prospect-field-label {
    padding: 0 10px 0 0;
    min-width: 60px;
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    line-height: 30px;
    color: #555;
}

.hc-prospect-profile-prospect-field-input {
    flex: 1;
    position: relative;
}

.hc-prospect-profile-prospect-field-input input {
    height: 30px;
    border-radius: 3px;
}

.hc-prospect-profile-prospect-field-input .hc-default-cell-label {
    height: 30px;
}

.hc-prospect-profile-prospect-field-input .hc-default-cell-label > span {
    line-height: 30px;
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
