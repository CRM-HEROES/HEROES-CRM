<template>
    <bloc icon="fa fa-users icon-brown" :name="$t('user.profile.blocs.groups')">
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus icon-blue"
                @click.prevent.stop="addGroup"
            />
        </template>
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <group-row
                    v-for="(group, i) in userGroups"
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
import { OPEN_SLIDE } from "@/actions/slide";

// Components
import Bloc from "@/components/user/profile/blocs/Bloc.vue";
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
            store.commit(OPEN_SLIDE, "user-manage-groups");
        },
    },

    computed: {
        ...mapGetters(["project", "userGroups"]),
    },
};
</script>
