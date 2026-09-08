<template>
    <item @click.prevent.stop="edit">
        <icon class="fa fa-phone" :size="30" />
        <div class="hc-item-main-content" v-text="line.name"></div>
        <div
            v-if="assignedUser"
            class="hc-item-count"
            v-text="assignedUser.name"
        ></div>
        <div class="hc-item-count" v-text="operatorLabel"></div>
        <icon tag="a" class="fa fa-cog" />
    </item>
</template>

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
            const operator = lineOperators.find(
                (o) => o.value === this.line.operator
            );
            return operator ? operator.label : this.line.operator;
        },

        /**
         *
         */
        assignedUser() {
            return this.users.find((u) => u.id == this.line.user_id);
        },
    },
};
</script>
