<template>
    <item @click.prevent.stop="edit">
        <!-- <icon class="fa fa-phone" :size="30" /> -->
        <div class="hc-item-main-content" v-text="lineTitle"></div>
        <!-- <icon
            v-if="assignedUser"
            class="hc-line-row-meta-icon fa fa-user"
            :title="assignedUser.name"
            :size="30"
        /> -->
        <span
            class="hc-line-row-operator-logo"
            :title="operatorLabel"
            :aria-label="operatorLabel"
        >
            <img
                v-if="operatorLogo"
                :src="operatorLogo"
                :alt="operatorLabel"
            />
            <icon v-else class="hc-line-row-meta-icon fa fa-phone" :size="30" />
        </span>
        <icon tag="a" class="fa fa-cog" />
    </item>
</template>

<style>
.hc-line-row-meta-icon {
    background: transparent;
    text-shadow: none;
}

.hc-line-row-operator-logo {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 30px;
    width: 30px;
    height: 30px;
    margin: 0 3px;
    background: transparent;
}

.hc-line-row-operator-logo img {
    display: block;
    max-width: 22px;
    max-height: 22px;
    object-fit: contain;
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
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "line-update");
            store.commit(SET_LINE, this.line);
        },
    },

    computed: {
        ...mapGetters(["users"]),

        /**
         *
         */
        operatorLabel() {
            const operator = this.operator;
            return operator ? operator.label : this.line.operator;
        },

        operatorLogo() {
            const operator = this.operator;
            return operator && operator.logo ? operator.logo : "";
        },

        operator() {
            return lineOperators.find((o) => o.value === this.line.operator);
        },

        /**
         *
         */
        assignedUser() {
            return this.users.find((u) => u.id == this.line.user_id);
        },

        lineTitle() {
            return this.assignedUser ? this.assignedUser.name : this.line.name;
        },
    },
};
</script>
