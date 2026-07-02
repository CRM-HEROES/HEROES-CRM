<template>
    <bloc icon="fa fa-link" :name="$t('prospect.profile.bloc.external_links')">
        <template #options>
            <icon tag="a" class="fa fa-plus" @click.prevent.stop="addLink" />
        </template>
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <link-row
                    v-for="(link, i) in prospectLinks"
                    :key="link.id"
                    :link="link"
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
import { SET_PROSPECT } from "@/actions/project/prospect";

// Components
import Bloc from "@/components/prospect/profile/blocs/Bloc.vue";
import LinkRow from "./LinkRow.vue";

export default {
    components: {
        Bloc,
        LinkRow,
    },

    methods: {
        /**
         * Add prospect link
         * See: @/components/prospect/link/add/Modal.vue
         */
        addLink() {
            store.commit(SET_PROSPECT, this.prospect);
            store.commit(OPEN_MODAL, "prospect-link-add");
        },
    },

    computed: {
        ...mapGetters(["project", "prospect", "prospectLinks"]),
    },
};
</script>
