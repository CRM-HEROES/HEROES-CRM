<template>
    <item @click.prevent.stop="edit()">
        <icon class="fa fa-phone" :size="30" />
        <div class="hc-item-main-content" v-text="lineTitle"></div>
        <div class="hc-line-row-operators">
            <button
                v-for="operatorLine in sortedLines"
                :key="operatorLine.id"
                type="button"
                class="hc-line-row-operator-logo"
                :title="operatorLabel(operatorLine)"
                :aria-label="operatorLabel(operatorLine)"
                @click.prevent.stop="edit(operatorLine)"
            >
                <img
                    v-if="operatorLogo(operatorLine)"
                    :src="operatorLogo(operatorLine)"
                    :alt="operatorLabel(operatorLine)"
                />
                <icon
                    v-else
                    class="hc-line-row-meta-icon fa fa-phone"
                    :size="16"
                />
            </button>
        </div>
        <icon tag="a" class="fa fa-cog" @click.prevent.stop="edit()" />
    </item>
</template>

<style>
.hc-line-row-meta-icon {
    background: transparent;
    text-shadow: none;
}

.hc-line-row-operators {
    display: flex;
    flex: 0 0 auto;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 2px;
    max-width: 104px;
    margin: 0 4px;
}

.hc-line-row-operator-logo {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 24px;
    width: 24px;
    height: 24px;
    padding: 0;
    border: none;
    background: transparent;
    cursor: pointer;
}

.hc-line-row-operator-logo img {
    display: block;
    max-width: 18px;
    max-height: 18px;
    object-fit: contain;
}

.hc-line-row-operator-logo:hover,
.hc-line-row-operator-logo:focus {
    background: rgba(18, 160, 243, 0.08);
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_LINE } from "@/actions/project/line";

// Constants
import lineOperators from "@/constants/lineOperators";

export default {
    props: {
        line: {
            type: Object,
            default: null,
        },

        lineGroup: {
            type: Object,
            default: null,
        },
    },

    methods: {
        edit(line = null) {
            const lineToEdit = line || this.primaryLine;

            if (!lineToEdit) {
                return;
            }

            store.commit(OPEN_MODAL, "line-update");
            store.commit(SET_LINE, lineToEdit);
        },

        operatorLabel(line) {
            const operator = this.operator(line);
            return operator ? operator.label : line.operator;
        },

        operatorLogo(line) {
            const operator = this.operator(line);
            return operator && operator.logo ? operator.logo : "";
        },

        operator(line) {
            return lineOperators.find((o) => o.value === line.operator);
        },
    },

    computed: {
        ...mapGetters(["users"]),

        /**
         *
         */
        lines() {
            if (this.lineGroup && Array.isArray(this.lineGroup.lines)) {
                return this.lineGroup.lines;
            }

            return this.line ? [this.line] : [];
        },

        sortedLines() {
            return [...this.lines].sort((a, b) =>
                this.operatorLabel(a).localeCompare(this.operatorLabel(b))
            );
        },

        primaryLine() {
            return this.sortedLines[0] || null;
        },

        /**
         *
         */
        assignedUser() {
            const userId = this.lineGroup
                ? this.lineGroup.user_id
                : this.primaryLine?.user_id;

            return this.users.find((u) => u.id == userId);
        },

        lineTitle() {
            if (this.assignedUser) {
                return this.assignedUser.name;
            }

            return this.lineGroup?.name || this.primaryLine?.name || "";
        },
    },
};
</script>
