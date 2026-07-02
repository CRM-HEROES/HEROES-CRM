<template>
    <bloc
        icon="fa fa-phone icon-orange"
        :name="$t('prospect.profile.bloc.calls')"
    >
        <template #options>
            <icon
                tag="a"
                class="fa fa-phone-alt"
                @click.prevent.stop="manageInteractions"
                v-if="prospect.phone_number || prospect.mobile_phone_number"
            />
        </template>
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <interaction-row
                    v-for="(interaction, i) in prospectInteractions"
                    :key="interaction.id"
                    :interaction="interaction"
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
import { OPEN_LEFT_SLIDE } from "@/actions/slide";
import { SET_INTERACTION_PROSPECT } from "@/actions/project/prospect/interaction";

// Components
import Bloc from "@/components/prospect/profile/blocs/Bloc.vue";
import InteractionRow from "./InteractionRow.vue";

export default {
    components: {
        Bloc,
        InteractionRow,
    },

    created() {
        store.commit(SET_INTERACTION_PROSPECT, this.prospect);
    },

    methods: {
        /**
         * Manage prospect interactions
         * See: @/components/prospect/interaction/Slide.vue
         */
        manageInteractions() {
            store.commit(SET_INTERACTION_PROSPECT, this.prospect);
            store.commit(OPEN_LEFT_SLIDE, "prospect-manage-interactions");
        },
    },

    watch: {
        prospect(newValue) {
            store.commit(SET_INTERACTION_PROSPECT, newValue);
        },
    },

    computed: {
        ...mapGetters(["project", "prospect", "prospectInteractions"]),
    },
};
</script>
