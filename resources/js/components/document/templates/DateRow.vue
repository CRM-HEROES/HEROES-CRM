<template>
    <field-template type="html" :content="'{date.' + date + '}'">
        <item class="hc-document-default-field">
            <icon class="fa fa-calendar" icon-size="11" :size="30" />
            <div class="hc-item-main-content" v-text="date"></div>
            <icon
                class="fa fa-arrows"
                icon-size="11"
                :size="30"
                color="#CCCCCC"
            />
        </item>
    </field-template>
</template>

<script>
import FieldTemplate from "../components/FieldTemplate.vue";

export default {
    components: {
        FieldTemplate,
    },

    props: {
        date: {
            type: String,
        },
    },
};
</script>
