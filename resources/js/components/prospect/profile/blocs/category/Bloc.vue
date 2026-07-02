<template>
    <bloc icon="fa fa-tags" :name="category.name">
        <template #body>
            <div style="padding: 10px; min-height: 40px">
                <template v-for="(label, i) in labels" :key="label.id">
                    <label-row
                        v-if="i <= shownLabelsCount"
                        :label="label"
                        :value="isLabelChecked(label)"
                    />
                </template>
            </div>

            <buttons v-if="labels.length > shownLabelsCount">
                <button
                    @click.prevent="showMoreLabels"
                    v-text="$t('show_more')"
                ></button>
            </buttons>
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";

// Actions

// Components
import Bloc from "@/components/prospect/profile/blocs/Bloc.vue";
import LabelRow from "./LabelRow.vue";

export default {
    components: {
        Bloc,
        LabelRow,
    },

    props: {
        bloc: {
            type: Object,
        },
        category: {
            type: Object,
        },
    },

    data() {
        return {
            shownLabelsCount: 20,
        };
    },

    methods: {
        /**
         *
         * @param {*} label
         */
        isLabelChecked(label) {
            return this.prospectLabels.findIndex((l) => l.id == label.id) >= 0;
        },

        /**
         *
         */
        showMoreLabels() {
            this.shownLabelsCount += 20;
        },
    },

    computed: {
        ...mapGetters(["project", "prospect", "categories", "prospectLabels"]),

        labels() {
            const category = this.categories.find(
                (c) => c.id == this.category.id
            );

            if (!category || !category.labels) {
                return [];
            }

            return [...category.labels].sort((a, b) =>
                this.prospectLabels.find((l) => l.id == a.id)
                    ? -1
                    : this.prospectLabels.find((l) => l.id == b.id)
                    ? 1
                    : 0
            );
        },
    },
};
</script>
