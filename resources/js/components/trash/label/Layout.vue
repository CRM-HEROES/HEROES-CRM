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

                <category-row
                    v-for="category in categories"
                    :key="category.id"
                    :category="category"
                    v-model="selectedCategories"
                />
                <label-row
                    v-for="label in labels"
                    :key="label.id"
                    :label="label"
                    v-model="selectedLabels"
                />
                <loading :loading="removing" />
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
import TrashService from "@/apis/project/trash";
import LabelRow from "./LabelRow.vue";
import CategoryRow from "./CategoryRow.vue";

export default {
    components: {
        LabelRow,
        CategoryRow,
    },

    data() {
        return {
            keyword: "",
            labels: [],
            categories: [],
            selectedLabels: [],
            selectedCategories: [],
            removing: false,
        };
    },

    mounted() {
        this.fetchLabels();
        this.fetchCategories();
    },

    methods: {
        async remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                try {
                    this.removing = true;

                    await TrashService.bulkDestroy(this.project.slug, {
                        labels: this.selectedLabels,
                        categories: this.selectedCategories,
                    });
                    this.fetchLabels();
                } finally {
                    this.removing = false;
                }
            });
        },

        async restore() {
            try {
                await TrashService.bulkRestore(this.project.slug, {
                    labels: this.selectedLabels,
                    categories: this.selectedCategories,
                });
                this.fetchLabels();
            } finally {
            }
        },

        /**
         * Fetch trashed labels
         */
        async fetchLabels() {
            const params = {};

            if (this.keyword) {
                params.query = this.keyword;
            }

            const { data } = await TrashService.label(this.project.slug, {
                params,
            });

            this.labels = data.data;
        },

        /**
         * Fetch trashed categories
         */
        async fetchCategories() {
            const params = {};

            if (this.keyword) {
                params.query = this.keyword;
            }

            const { data } = await TrashService.category(this.project.slug, {
                params,
            });

            this.categories = data.data;
        },
    },

    computed: {
        ...mapGetters(["project"]),

        all: {
            get() {
                return (
                    this.selectedLabels.length == this.labels.length &&
                    this.selectedCategories.length == this.categories.length
                );
            },
            set(value) {
                this.selectedLabels = value
                    ? this.labels.map((thread) => thread.id)
                    : [];
                this.selectedCategories = value
                    ? this.categories.map((thread) => thread.id)
                    : [];
            },
        },
    },
};
</script>
