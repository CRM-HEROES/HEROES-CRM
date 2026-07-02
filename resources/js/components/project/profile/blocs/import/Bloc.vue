<template>
    <bloc icon="fa fa-file-upload" :name="$t('project.profile.blocs.imports')">
        <template #options>
            <icon tag="a" class="fa fa-plus" @click.prevent.stop="addImport" />
            <icon v-if="imports.length > 0" class="fa fa-caret-down" />
        </template>
        <template #body>
            <div
                style="padding: 10px 10px; float: left; width: 100%"
                v-if="imports.length > 0"
            >
                <import-row
                    v-for="(prospectImport, i) in imports"
                    :key="prospectImport.id"
                    :prospect-import="prospectImport"
                />
            </div>
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";

// Components
import Bloc from "@/components/project/profile/blocs/Bloc.vue";
import ImportRow from "./ImportRow.vue";

export default {
    components: {
        Bloc,
        ImportRow,
    },

    methods: {
        /**
         * Add import
         * See: @/components/import/add/Modal.vue
         */
        addImport() {
            store.commit(OPEN_MODAL, "import-add");
        },
    },

    computed: {
        ...mapGetters(["project", "imports"]),
    },
};
</script>
