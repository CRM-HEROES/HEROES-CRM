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
                <line-row v-for="line in lines" :key="line.id" :line="line" />
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

// Components
import Bloc from "@/components/project/profile/blocs/Bloc.vue";
import LineRow from "./LineRow.vue";

export default {
    components: {
        Bloc,
        LineRow,
    },

    methods: {
        /**
         * Add line
         * See: @/components/line/add/Modal.vue
         */
        addLine() {
            store.commit(OPEN_MODAL, "line-add");
        },
    },

    computed: {
        ...mapGetters(["project", "lines"]),
    },
};
</script>
