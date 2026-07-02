<template>
    <div class="hc-interaction-cell">
        <span
            class="hc-interaction-cell-count"
            v-if="interactions.length > 1"
            v-text="interactions.length"
        ></span>
        <interaction-row
            v-for="interaction in interactions"
            :key="interaction.id"
            :interaction="interaction"
            :prospect="prospect"
        />
    </div>
</template>

<script>
// Components
import InteractionRow from "./InteractionRow.vue";

export default {
    components: {
        InteractionRow,
    },

    props: {
        /**
         * Prospect
         */
        prospect: {
            type: Object,
        },
    },

    computed: {
        /**
         */
        interactions() {
            if (!this.prospect.interactions) {
                return [];
            }

            const interactions = [...this.prospect.interactions];

            interactions.sort((m1, m2) =>
                m2.created_at > m1.created_at ? 1 : -1
            );

            return interactions;
        },
    },
};
</script>
