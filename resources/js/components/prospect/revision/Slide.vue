<template>
    <slide
        name="prospect-manage-revisions"
        :title="
            $t('prospect.revision_history.title', {
                prospect: prospectFullName,
            })
        "
        :url="
            prospect
                ? {
                      name: 'prospect.show',
                      params: {
                          project: project.slug,
                          prospect: prospect.id,
                      },
                  }
                : null
        "
        icon="fa fa-pen"
        style="width: 360px"
        @open="fetchRevisions"
        @first-open="scrollListener"
    >
        <div id="hc-prospect-revisions" style="height: 100%; overflow: auto">
            <item-list id="hc-prospect-revisions-content" padding="12px">
                <template v-for="(revision, i) in revisions" :key="revision.id">
                    <div
                        v-if="
                            i == 0 ||
                            revision.created_at.substring(0, 10) !=
                                revisions[i - 1].created_at.substring(0, 10)
                        "
                        :key="revision.created_at.substring(0, 10)"
                        class="hc-revision-date"
                        v-text="revisionDate(revision)"
                    ></div>

                    <revision-row :revision="revision" />
                </template>
            </item-list>
            <loading :loading="page == 1 && fetchingRevisions" />
        </div>
    </slide>
</template>

<style>
.hc-revision-date {
    width: 100%;
    text-align: right;
    height: 35px;
    line-height: 35px;
    font-weight: 500;
    opacity: 0.7;
    border-bottom: 1px solid #0002;
    margin-bottom: 5px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { FETCH_PROSPECT_REVISIONS } from "@/actions/project/prospect/revision";

// Components
import RevisionRow from "./RevisionRow.vue";

export default {
    components: {
        RevisionRow,
    },

    data() {
        return {
            fetchingRevisions: false,
            revisions: [],
            page: 1,
        };
    },

    methods: {
        async fetchRevisions() {
            if (this.page == 0) {
                return;
            }

            this.fetchingRevisions = true;

            try {
                const data = await store.dispatch(FETCH_PROSPECT_REVISIONS);

                this.revisions = [...this.revisions, ...data.data];

                if (this.page < data.last_page) {
                    this.page++;
                } else {
                    this.page = 0;
                }
            } finally {
                this.fetchingRevisions = false;
            }
        },

        revisionDate(revision) {
            return dayjs(revision.created_at).format("DD/MM/YYYY");
        },

        scrollListener() {
            let prospectRevisions = document.getElementById(
                "hc-prospect-revisions"
            );
            let prospectRevisionsContent = document.getElementById(
                "hc-prospect-revisions-content"
            );

            prospectRevisions.addEventListener("scroll", (event) => {
                if (
                    prospectRevisions.scrollTop >=
                        prospectRevisionsContent.getBoundingClientRect()
                            .height -
                            prospectRevisions.getBoundingClientRect().height -
                            100 &&
                    !this.fetchingRevisions
                ) {
                    this.fetchRevisions();
                }
            });
        },
    },

    watch: {},

    computed: {
        ...mapGetters(["project", "prospect", "prospectFullName"]),
    },
};
</script>
