<template>
    <item tag="label">
        <icon class="fa fa-tag" :style="style" />
        <div class="hc-item-main-content" v-text="label.name"></div>
        <icon
            v-if="can('all.project.category.label.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent="edit"
        />
        <icon
            tag="a"
            class="fa fa-thumbs-down"
            :style="excludeStyle"
            :size="30"
            :title="
                $t('prospect.table.filters.label.without_label', {
                    label: label.name,
                })
            "
            @click.prevent="toggleExclude"
        />
        <checkbox :model-value="value" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_LABEL } from "@/actions/project/label";

export default {
    props: {
        value: {
            type: Boolean,
            default: false,
        },

        excluded: {
            type: Boolean,
            default: false,
        },

        label: {
            type: Object,
        },
    },

    data() {
        return {
            currentValue: this.value,
        };
    },

    methods: {
        change(event) {
            this.$emit("checked", this.label, event.target.checked);
        },

        toggleExclude() {
            this.$emit("exclude", this.label, !this.excluded);
        },

        edit() {
            store.commit(OPEN_MODAL, "label-update");
            store.commit(SET_LABEL, this.label);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        style() {
            return {
                color: this.label.bgcolor,
            };
        },

        /**
         *
         */
        excludeStyle() {
            return {
                color: this.excluded ? "#CC0000" : "#CCCCCC",
            };
        },
    },
};
</script>
