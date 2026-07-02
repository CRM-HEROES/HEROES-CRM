<template>
    <movable
        :classes="[
            'hc-document-field',
            isNew ? 'is-new' : '',
            selected ? 'selected' : '',
            removing ? 'removing' : '',
            editing ? 'editing' : '',
        ]"
        :style="field.style"
        :zoom="documentZoom"
        @dragging="selecting"
        @w-resizing="selecting"
        @h-resizing="selecting"
        @wh-resizing="selecting"
        @rotating="selecting"
        @changing="update"
        @change="change"
        @click.stop
    >
        <!--
            Field content
        -->
        <div
            class="hc-document-field-content"
            :style="contentStyle"
            v-html="renderedContent"
            @dblclick.stop="setFieldToEdit(field)"
        ></div>
        <field-autocomplete
            class="hc-document-field-content-edit"
            ref="autocomplete"
            v-model.lazy="content"
            @mouseup.stop
            @mousedown.stop
            @blured="setFieldToEdit(null)"
            @changed="updateContent($event.target.value)"
            @selected="checkItemType"
        />

        <!--
            Field remove
        -->
        <div
            class="hc-document-field-remove"
            @mousedown.stop="remove"
            v-if="!isNew"
        >
            &times;
        </div>
    </movable>
</template>

<style>
.hc-document-field.selected {
    outline: 2px solid #7939b8 !important;
}

.hc-document-field.removing {
    transition: transform ease-out 70ms, opacity ease-out 70ms;
    transform: scale(0.85);
    opacity: 0;
}

.hc-document-field.is-new {
    box-shadow: 0 4px 8px #0005;
    transform: scale(1.005);
    z-index: 1000;
}

.hc-document-field.editing {
    z-index: 1000;
    outline: none !important;
}

.hc-document-field.editing .hc-document-field-content {
    height: 0;
    overflow: hidden;
}

.hc-document-field .hc-document-field-content-edit {
    display: none;
    width: 100%;
    border: none;
    outline: 2px solid #7939b8 !important;
}

.hc-document-field.editing .hc-document-field-content-edit {
    display: block;
}

.hc-document-field.is-new {
    position: fixed;
}

.hc-document-field:hover {
    outline: 2px solid #7939b8;
}

.hc-document-field-remove {
    background-color: #7939b8;
    border-radius: 50%;
    color: white;
    height: 22px;
    font-size: 12px;
    left: -15px;
    line-height: 24px;
    opacity: 0;
    position: absolute;
    text-align: center;
    top: -15px;
    transform: scale(0.7);
    transition: opacity ease-out 100ms, transform ease-out 100ms;
    width: 22px;
}

.hc-document-field:hover > .hc-document-field-remove {
    opacity: 1;
    transform: scale(1);
}

.hc-document-field.is-new .hc-movable-h-resize,
.hc-document-field.is-new .hc-movable-w-resize,
.hc-document-field.is-new .hc-movable-wh-resize,
.hc-document-field.is-new .hc-movable-rotate,
.hc-document-field.is-new .hc-movable-remove {
    display: none;
}

.hc-document-field-content {
    height: 100%;
}
</style>

<script>
import store from "@/store";
import { mapGetters } from "vuex";

// Actions
import {
    UPDATE_DOCUMENT_FIELD,
    REMOVE_DOCUMENT_FIELD,
    SET_DOCUMENT_FIELD_EDIT,
} from "@/actions/project/document";

// Components
import Movable from "@/components/Movable.vue";
import FieldAutocomplete from "./FieldAutocomplete";

export default {
    components: {
        Movable,
        FieldAutocomplete,
    },

    props: {
        /**
         *
         */
        field: {
            type: Object,
        },

        /**
         * List of renderers
         */
        renderers: {
            type: Array,
            default: [],
        },

        /**
         * Is field selected
         */
        selected: {
            type: Boolean,
            default: false,
        },

        /**
         * Is field selecting
         */
        isNew: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            removing: false,
        };
    },

    mounted() {
        if (this.editing) {
            this.$refs.autocomplete.focus();
        }
    },

    methods: {
        /**
         *
         */
        remove() {
            this.removing = true;
            store.dispatch(REMOVE_DOCUMENT_FIELD, this.field.id);
        },

        /**
         *
         */
        selecting(event) {
            this.$emit("selected", event, this.field);
        },

        /**
         *
         */
        update(style) {
            this.field.style = {
                ...this.field.style,
                ...style,
            };
        },

        /**
         *
         */
        change(style) {
            if (!this.isNew) {
                this.field.style = {
                    ...this.field.style,
                    ...style,
                };

                store.dispatch(UPDATE_DOCUMENT_FIELD, {
                    id: this.field.id,
                    style: this.field.style,
                });
            }
        },

        async setFieldToEdit(field) {
            store.commit(SET_DOCUMENT_FIELD_EDIT, field);

            if (field) {
                await this.$nextTick();
                this.$refs.autocomplete.focus();
            }
        },

        checkItemType(item) {},

        updateContent(value) {
            if (this.isNew) {
                this.$emit("add-field", {
                    ...this.field,
                    content: value,
                });
            } else {
                store.dispatch(UPDATE_DOCUMENT_FIELD, {
                    id: this.field.id,
                    content: value,
                });
            }
        },
    },

    computed: {
        /**
         * Content style
         */
        contentStyle() {
            return Object.fromEntries(
                Object.entries(this.field.style).filter(
                    ([key]) =>
                        key != "left" &&
                        key != "top" &&
                        key != "width" &&
                        key != "height" &&
                        key != "transform"
                )
            );
        },

        /**
         * Rendered content
         */
        renderedContent() {
            let field = { ...this.field };

            this.renderers.forEach((renderer) => {
                field.content = renderer.render(field);
            });

            return field.content;
        },

        content() {
            return this.field.content;
        },

        editing() {
            return (
                this.documentFieldEdit &&
                (this.documentFieldEdit.id === this.field.id ||
                    (!this.documentFieldEdit.id && this.isNew))
            );
        },

        // Store
        ...mapGetters(["documentZoom", "documentFieldEdit"]),
    },
};
</script>
