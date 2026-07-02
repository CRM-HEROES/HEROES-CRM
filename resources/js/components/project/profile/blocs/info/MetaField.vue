<template>
    <label class="hc-project-profile-project-field">
        <div
            class="hc-project-profile-project-field-label"
            v-text="field.name"
        ></div>

        <div class="hc-project-profile-project-field-input">
            <meta-cell
                :project="project"
                :field="field.slug"
                :type="field.type"
                :placeholder="field.name"
            />
        </div>
    </label>
</template>

<script>
import MetaCell from "@/components/project/table/cell/MetaCell.vue";

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
        project: {
            type: Object,
        },
    },
};
</script>
