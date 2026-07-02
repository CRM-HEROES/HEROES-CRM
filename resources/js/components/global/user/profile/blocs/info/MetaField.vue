<template>
    <label class="hc-user-profile-user-field">
        <div class="hc-user-profile-user-field-label" v-text="field.name"></div>

        <div class="hc-user-profile-user-field-input">
            <meta-cell
                :user="user"
                :field="field.slug"
                :type="field.type"
                :placeholder="field.name"
            />
        </div>
    </label>
</template>

<script>
import MetaCell from "@/components/user/table/cell/MetaCell.vue";

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
        user: {
            type: Object,
        },
    },
};
</script>
