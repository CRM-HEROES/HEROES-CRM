<template>
    <bloc icon="fa fa-tags icon-purple" :name="'Filtres actifs'">
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus"
                @click.prevent.stop="manageLabels"
            />
        </template>
        <template #body>
            <div style="padding: 10px; min-height: 40px">
                <label-row
                    v-for="label in prospectLabels"
                    :key="label.id"
                    :label="label"
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
import { OPEN_SLIDE } from "@/actions/slide";
import { SET_PROSPECT_CATEGORY } from "@/actions/project/prospect";

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
    },

    methods: {
        /**
         * Manage prospect labels
         * See: @/components/prospect/label/Slide.vue
         */
        manageLabels() {
            store.commit(SET_PROSPECT_CATEGORY, null);
            store.commit(OPEN_SLIDE, "prospect-manage-labels");
        },
    },

    computed: {
        ...mapGetters(["project", "prospect", "prospectLabels"]),
    },
};
</script>
