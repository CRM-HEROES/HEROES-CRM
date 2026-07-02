<template>
    <div class="hc-flex-column" style="height: 100%">
        <div class="hc-flex-column hc-flex-1" style="overflow: hidden">
            <search v-model="keyword" />
            <item-list class="hc-flex-1" padding="5px" style="overflow: auto">
                <item tag="label">
                    <icon class="fa fa-check-square" />
                    <div class="hc-item-main-content" v-text="$t('all')"></div>

                    <checkbox v-model="all" />
                </item>

                <prospect-row
                    v-for="prospect in prospects"
                    :key="prospect.id"
                    :prospect="prospect"
                    v-model="selected"
                />
                <loading :loading="removing || restoring" />
            </item-list>
            <buttons>
                <button
                    @click.prevent="restore"
                    v-text="$t('restore')"
                ></button>
                <button
                    @click.prevent="remove"
                    class="hc-button-danger"
                    v-text="$t('delete')"
                ></button>
            </buttons>
        </div>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import ProspectService from "@/apis/project/prospect";

import {
    BULK_FORCE_REMOVE_PROSPECT,
    BULK_RESTORE_PROSPECT,
} from "@/actions/project/prospect";

import ProspectRow from "./ProspectRow.vue";

export default {
    components: {
        ProspectRow,
    },

    data() {
        return {
            keyword: "",
            prospects: [],
            selected: [],
            removing: false,
        };
    },

    mounted() {
        this.fetch();
    },

    methods: {
        async remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                try {
                    this.removing = true;

                    await store.dispatch(
                        BULK_FORCE_REMOVE_PROSPECT,
                        this.selected
                    );
                    this.fetch();
                } finally {
                    this.removing = false;
                }
            });
        },

        async restore() {
            this.restoring = true;

            try {
                await store.dispatch(BULK_RESTORE_PROSPECT, this.selected);
                this.fetch();
            } finally {
                this.restoring = false;
            }
        },

        /**
         * Fetch trashed prospects
         */
        async fetch() {
            const filters = { trashed: 1 };

            if (this.keyword) {
                filters.query = this.keyword;
            }

            const { data } = await ProspectService.get(this.project.slug, {
                params: {
                    filters: JSON.stringify(filters),
                },
            });

            this.prospects = data.data;
        },
    },

    computed: {
        ...mapGetters(["project"]),

        all: {
            get() {
                return this.selected.length == this.prospects.length;
            },
            set(value) {
                this.selected = value
                    ? this.prospects.map((prospect) => prospect.id)
                    : [];
            },
        },
    },
};
</script>
