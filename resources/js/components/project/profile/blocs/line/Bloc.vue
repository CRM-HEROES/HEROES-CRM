<template>
    <bloc icon="fa fa-phone" :name="$t('project.profile.blocs.lines')">
        <template #options>
            <icon tag="a" class="fa fa-plus" @click.prevent.stop="addLine" />
            <icon v-if="lines.length > 0" class="fa fa-caret-down" />
        </template>
        <template #body>
            <div
                style="padding: 10px 10px; float: left; width: 100%"
                v-if="lines.length > 0"
            >
                <line-row
                    v-for="lineGroup in lineGroups"
                    :key="lineGroup.key"
                    :line-group="lineGroup"
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

// Constants
import lineOperators from "@/constants/lineOperators";

// Components
import Bloc from "@/components/project/profile/blocs/Bloc.vue";
import LineRow from "./LineRow.vue";

export default {
    components: {
        Bloc,
        LineRow,
    },

    computed: {
        ...mapGetters(["project", "lines"]),

        lineGroups() {
            const groups = {};

            this.lines.forEach((line) => {
                const key = line.user_id
                    ? "user-" + line.user_id
                    : "line-" + line.id;

                if (!groups[key]) {
                    groups[key] = {
                        key,
                        user_id: line.user_id,
                        name: line.name,
                        lines: [],
                    };
                }

                groups[key].lines.push(line);
            });

            return Object.values(groups).map((group) => ({
                ...group,
                lines: group.lines.sort((a, b) =>
                    this.operatorLabel(a).localeCompare(this.operatorLabel(b))
                ),
            }));
        },

        operatorLabels() {
            return lineOperators.reduce((labels, operator) => {
                labels[operator.value] = operator.label;
                return labels;
            }, {});
        },
    },

    methods: {
        /**
         * Add line
         * See: @/components/line/add/Modal.vue
         */
        addLine() {
            store.commit(OPEN_MODAL, "line-add");
        },

        operatorLabel(line) {
            return this.operatorLabels[line.operator] || line.operator || "";
        },
    },
};
</script>
