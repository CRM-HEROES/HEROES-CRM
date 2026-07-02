<template>
    <slide
        :name="name"
        :title="$t('campaign.action.prospect.attach_group.title')"
        icon="fa fa-users"
        style="width: 260px"
    >
        <div
            class="hc-flex-1 hc-flex-column"
            style="height: 100%; overflow: hidden; position: relative"
        >
            <search v-model="groupKeyword" />
            <item-list class="hc-flex-1" padding="12px">
                <group-row
                    v-for="group in filteredGroups"
                    :key="group.id"
                    :group="group"
                    @dragging="dragging"
                    @dragged="dragged"
                />
            </item-list>
        </div>
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { CLOSE_SLIDE } from "@/actions/slide";

import GroupRow from "./GroupRow.vue";

export default {
    components: {
        GroupRow,
    },

    data() {
        return {
            name: "campaign-action-prospect-attach-group",
            tab: 0,
            groupKeyword: "",
        };
    },

    methods: {
        dragging(field) {
            this.$emit("dragging", field);
        },

        dragged(x, y) {
            this.$emit("dragged", x, y);
            store.commit(CLOSE_SLIDE);
        },
    },

    computed: {
        ...mapGetters(["groups", "slideOpen"]),

        /**
         *
         */
        filteredGroups() {
            const keyword = removeStringAccent(this.groupKeyword);

            return this.groups.filter(
                (group) => removeStringAccent(group.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
