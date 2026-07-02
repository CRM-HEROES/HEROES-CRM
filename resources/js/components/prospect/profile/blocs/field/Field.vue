<template>
    <div>
        <template v-if="field">
            <meta-field v-if="field.meta" :field="field" :prospect="prospect" />
            <default-field v-else :field="field" :prospect="prospect" />
        </template>
    </div>
</template>

<script>
import DefaultField from "./DefaultField.vue";
import MetaField from "./MetaField.vue";

export default {
    components: {
        DefaultField,
        MetaField,
    },

    props: {
        field: {},
        prospect: {},
    },
};
</script>
