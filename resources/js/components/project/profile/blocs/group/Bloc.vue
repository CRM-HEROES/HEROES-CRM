<template>
    <bloc icon="fa fa-users" :name="$t('project.profile.blocs.groups')">
        <template #options>
            <icon tag="a" class="fa fa-plus" @click.prevent.stop="addGroup" />
            <icon v-if="groups.length > 0" class="fa fa-caret-down" />
        </template>
        <template #body>
            <div
                style="padding: 10px 10px; float: left; width: 100%"
                v-if="groups.length > 0"
            >
                <group-row
                    v-for="(group, i) in groups"
                    :key="group.id"
                    :group="group"
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
import GroupRow from "./GroupRow.vue";

export default {
    components: {
        Bloc,
        GroupRow,
    },

    methods: {
        /**
         * Add group
         * See: @/components/group/add/Modal.vue
         */
        addGroup() {
            store.commit(OPEN_MODAL, "group-add");
        },
    },

    computed: {
        ...mapGetters(["project", "groups"]),
    },
};
</script>
