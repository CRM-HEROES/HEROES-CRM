<template>
    <bloc
        icon="fa fa-clock icon-brown"
        :name="$t('prospect.profile.bloc.history')"
    >
        <template #body>
            <div id="hc-prospect-profile-history">
                <history-items :items="histories" />
            </div>
        </template>
    </bloc>
</template>

<style>
#hc-prospect-profile-history {
    padding: 20px 20px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import { assignmentRecorded } from "@/utils/prospect-label-history";

import { FETCH_PROSPECT_REVISIONS } from "@/actions/project/prospect/revision";

// Components
import Bloc from "@/components/prospect/profile/blocs/Bloc.vue";
import HistoryItems from "./Items.vue";

export default {
    components: {
        Bloc,
        HistoryItems,
    },

    created() {
        this.fetchRevisions();
    },

    methods: {
        async fetchRevisions() {
            try {
                await store.dispatch(FETCH_PROSPECT_REVISIONS, { all: true });
            } finally {
            }
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "prospect",
            "prospectEvents",
            "prospectFiles",
            "prospectGroups",
            "prospectInteractions",
            "prospectLabels",
            "prospectLinks",
            "prospectMessages",
            "prospectOrders",
            "prospectRevisions",
            "prospectSms",
            "prospectUsers",
        ]),

        histories() {
            const revisions = this.prospectRevisions.data || [];
            return [
                ...this.prospectEvents.map((item) => ({
                    ...item,
                    type: "event",
                })),
                ...this.prospectFiles.map((item) => ({
                    ...item,
                    type: "file",
                })),
                ...this.prospectGroups.map((item) => ({
                    ...item,
                    type: "group",
                })),
                ...this.prospectInteractions.map((item) => ({
                    ...item,
                    type: "interaction",
                })),
                ...this.prospectLabels.filter(
                    (item) => !assignmentRecorded(item, revisions)
                ).map((item) => ({
                    ...item,
                    type: "label",
                    created_at: (item.pivot && item.pivot.created_at) || item.created_at,
                })),
                ...this.prospectLinks.map((item) => ({
                    ...item,
                    type: "link",
                })),
                ...this.prospectMessages.map((item) => ({
                    ...item,
                    type: "message",
                })),
                ...this.prospectOrders.map((item) => ({
                    ...item,
                    type: "order",
                })),
                ...revisions.map((item) => ({
                    ...item,
                    type: "revision",
                })),
                ...this.prospectSms.map((item) => ({
                    ...item,
                    type: "sms",
                })),
                ...this.prospectUsers.map((item) => ({
                    ...item,
                    type: "user",
                })),
            ]
                .filter((item) => item.created_at)
                .sort((item1, item2) =>
                    new Date(item2.created_at) - new Date(item1.created_at) ||
                    item2.id - item1.id
                );
        },
    },
};
</script>
