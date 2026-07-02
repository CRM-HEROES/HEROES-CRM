<template>
    <label class="hc-project-profile-project-field">
        <div
            class="hc-project-profile-project-field-label"
            v-text="field.name"
        ></div>

        <div class="hc-project-profile-project-field-input">
            <street-cell
                v-if="field.slug == 'street'"
                :project="project"
                :field="field.slug"
            />
            <default-cell
                v-else
                :project="project"
                :field="field.slug"
                :placeholder="field.name"
            />
        </div>
    </label>
</template>

<style>
.hc-project-profile-project-field {
    width: 100%;
    min-height: 26px;
    display: flex;
    flex-direction: row;
    font-size: 12px;
    overflow: hidden;
}

.hc-project-profile-project-field-label {
    padding: 0 10px 0 0;
    min-width: 60px;
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    line-height: 26px;
    color: #555;
}

.hc-project-profile-project-field-input {
    flex: 1;
    position: relative;
}

.hc-project-profile-project-field-input input {
    height: 26px;
}

.hc-project-profile-project-field-input .hc-default-cell-label {
    height: 26px;
}

.hc-project-profile-project-field-input .hc-default-cell-label > span {
    line-height: 26px;
}
</style>

<script>
import DefaultCell from "@/components/project/table/cell/DefaultCell.vue";
import StreetCell from "@/components/project/table/cell/StreetCell.vue";

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
        project: {
            type: Object,
        },
    },
};
</script>
