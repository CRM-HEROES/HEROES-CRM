<template>
    <item
        :class="[
            'hc-prospect-import-column',
            mapping ? 'hc-prospect-import-column-mapped' : '',
        ]"
    >
        <icon v-if="mapping" class="fa fa-link" />
        <icon v-else class="fa fa-columns" />

        <div class="hc-item-main-content" v-text="column.header"></div>

        <div
            v-if="mapping"
            class="hc-prospect-import-column-mapping"
            v-text="mapping"
        ></div>
        <icon
            v-else
            class="fa fa-plus icon-green hc-prospect-import-column-auto-add"
        />

        <item-list v-if="!mapping" class="hc-prospect-import-column-auto-list">
            <item @click.stop="$emit('add-field', column)">
                <icon class="fa fa-keyboard icon-blue" :size="26" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('import.process.tab.mapping.shortcut_new_field')"
                ></div>
            </item>
            <item @click.stop="$emit('add-category', column)">
                <icon class="fa fa-tags icon-garnet" :size="26" />
                <div
                    class="hc-item-main-content"
                    v-text="
                        $t('import.process.tab.mapping.shortcut_new_category')
                    "
                ></div>
            </item>
            <item @click.stop="autoMapping('thread->')">
                <icon class="fa fa-envelope icon-green" :size="26" />
                <div
                    class="hc-item-main-content"
                    v-text="
                        $t('import.process.tab.mapping.shortcut_new_thread')
                    "
                ></div>
            </item>
            <loading :loading="autoMappingProcessing" />
        </item-list>

        <icon
            v-if="prospectImport.source == 'webservice'"
            class="fa fa-times icon-red"
            @click.prevent.stop="removeColumn"
        />
        <icon
            v-if="prospectImport.source == 'webservice'"
            class="fa fa-pen icon-blue"
            @click.prevent.stop="editColumn"
        />
        <icon
            v-if="prospectImport.source != 'webservice'"
            class="fa fa-caret-right"
        />
    </item>
</template>

<style scoped>
.hc-prospect-import-column {
    overflow: visible;
}
.hc-prospect-import-column > .hc-item-main-content {
    color: #333333;
}
.hc-prospect-import-column > .hc-icon {
    color: #bbbbbb;
}
.hc-prospect-import-column-mapped > .hc-icon {
    color: #333333;
}
.hc-prospect-import-column-mapped > .hc-item-main-content {
    color: #111111;
}
.hc-prospect-import-column-mapping {
    color: #2891d2;
    max-width: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
}
.hc-prospect-import-column-auto-list {
    display: none;
    position: absolute;
    top: 30px;
    right: 0;
    box-shadow: 0 2px 10px -5px #0007;
    height: auto;
    background-color: white;
    z-index: 1;
    width: auto;
    padding: 7px;
}
.hc-prospect-import-column-auto-add:hover
    + .hc-prospect-import-column-auto-list {
    display: block;
}
.hc-prospect-import-column-auto-list:hover {
    display: block;
}
</style>

<script>
import store from "@/store";
import { UPDATE_IMPORT } from "@/actions/project/import";

export default {
    props: {
        prospectImport: {
            type: Object,
        },
        column: {
            type: String,
        },
        mapping: {
            type: String,
        },
    },

    data() {
        return {
            autoMappingProcessing: false,
        };
    },

    methods: {
        removeColumn() {
            store.dispatch(UPDATE_IMPORT, {
                id: this.prospectImport.id,
                headers: this.prospectImport.headers.filter(
                    (header, index) => index != this.column.index
                ),
                mapping: this.prospectImport.mapping.filter(
                    (header, index) => index != this.column.index
                ),
            });
        },

        editColumn() {
            this.$emit("edit", this.column.index);
        },

        async autoMapping(mapping) {
            this.autoMappingProcessing = true;
            try {
                await store.dispatch(UPDATE_IMPORT, {
                    id: this.prospectImport.id,
                    mapping: this.prospectImport.mapping.map((header, index) =>
                        index == this.column.index ? mapping : header
                    ),
                });
            } finally {
                this.autoMappingProcessing = false;
            }
        },
    },
};
</script>
