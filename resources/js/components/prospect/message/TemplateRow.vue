<template>
    <div class="hc-prospect-message-template-row">
        <item>
            <icon class="fa fa-file-alt" />
            <div class="hc-item-main-content" v-text="template.name"></div>
            <icon
                v-if="can('all.project.message-template.update')"
                class="fa fa-cog hc-show-on-hover"
                @click.stop.prevent="edit"
            />
            <icon
                class="fa fa-paper-plane icon-purple-hover"
                @click.stop="$emit('send')"
                v-tooltip="$t('prospect.message.templates.send')"
            />
        </item>
        <div
            class="hc-prospect-message-template-row-content"
            v-html="template.body"
        ></div>
    </div>
</template>

<style>
.hc-prospect-message-template-row {
    border: 1px solid #eee;
    border-radius: 8px;
    cursor: pointer;
}

.hc-prospect-message-template-row-content {
    width: 100%;
    /*max-height: 130px;*/
    overflow: hidden;
    padding: 8px;
    background-color: #f5f5f5;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_MESSAGE_TEMPLATE } from "@/actions/project/message-template";

export default {
    props: {
        template: {
            type: Object,
        },
    },

    methods: {
        edit(e) {
            store.commit(OPEN_MODAL, "message-template-update");
            store.commit(SET_MESSAGE_TEMPLATE, this.template);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        style() {
            return {
                color: this.template.bgcolor,
            };
        },
    },
};
</script>
