<template>
    <item class="hc-document-font">
        <div
            class="hc-item-main-content"
            v-text="font.name"
            :style="{
                'font-family': font.name,
                'font-weight': font.weight,
                'font-style': font.style,
            }"
        ></div>
        <icon
            v-if="font.style == 'italic'"
            class="fa fa-italic"
            icon-size="9"
            :size="20"
        />
        <icon
            v-if="font.weight == 'bold'"
            class="fa fa-bold"
            icon-size="9"
            :size="20"
        />
        <icon
            tag="a"
            class="fa fa-trash"
            @click.prevent.stop="remove"
            color="#333333"
            icon-size="11"
            :size="30"
        />
        <loading :loading="removingFont" />
    </item>
</template>

<style>
.hc-document-font .hc-item-main-content {
    font-size: 16px;
}
</style>

<script>
import { REMOVE_DOCUMENT_FONT } from "@/actions/project/document";

export default {
    props: {
        font: {
            type: Object,
        },
    },

    data() {
        return {
            removingFont: false,
        };
    },

    methods: {
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingFont = true;

                try {
                    await store.dispatch(REMOVE_DOCUMENT_FONT, this.font.id);
                } finally {
                    this.removingFont = false;
                }
            });
        },
    },
};
</script>
