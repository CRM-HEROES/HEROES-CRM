<template>
    <bloc
        icon="fa fa-users icon-brown"
        :name="$t('prospect.profile.bloc.affected_groups')"
    >
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus"
                @click.prevent.stop="manageGroups"
            />
        </template>
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <group-row
                    v-for="(group, i) in prospectGroups"
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
import { SET_PROSPECT } from "@/actions/project/prospect";

// Components
import Bloc from "@/components/prospect/profile/blocs/Bloc.vue";
import GroupRow from "./GroupRow.vue";

export default {
    components: {
        Bloc,
        GroupRow,
    },

    methods: {
        /**
         * Manage prospect groups
         * See: @/components/prospect/group/Slide.vue
         */
        manageGroups() {
            store.commit(SET_PROSPECT, this.prospect);
            store.commit(OPEN_SLIDE, "prospect-manage-groups");
        },
    },

    computed: {
        ...mapGetters(["project", "prospect", "prospectGroups"]),
    },
};
</script>
