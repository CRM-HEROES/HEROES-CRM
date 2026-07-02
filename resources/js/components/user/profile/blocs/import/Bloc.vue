<template>
    <bloc
        icon="fa fa-file-upload icon-green"
        :name="$t('user.profile.blocs.imports')"
    >
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus icon-blue"
                @click.prevent.stop="addImport"
            />
        </template>
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <import-row
                    v-for="(userImport, i) in user.imports ? user.imports : []"
                    :key="userImport.id"
                    :user-import="userImport"
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
import Bloc from "@/components/user/profile/blocs/Bloc.vue";
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
        ...mapGetters(["project", "user"]),
    },
};
</script>
