<template>
    <bloc icon="fa fa-robot" :name="$t('project.profile.blocs.agents')">
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus"
                :title="$t('agent.new')"
                @click.prevent.stop="addAgent"
            />
            <icon v-if="projectAgents.length > 0" class="fa fa-caret-down" />
        </template>
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <p v-if="error" class="hc-agent-bloc-error" role="alert">
                    {{ error }}
                </p>
                <p
                    v-else-if="loaded && projectAgents.length === 0"
                    class="hc-agent-bloc-empty"
                >
                    {{ $t("agent.bloc_empty") }}
                </p>
                <agent-row
                    v-for="agent in projectAgents"
                    :key="agent.id"
                    :agent="agent"
                />
            </div>
        </template>
    </bloc>
</template>

<style>
.hc-agent-bloc-empty {
    color: #777;
    margin: 0;
}

.hc-agent-bloc-error {
    color: #b00020;
    margin: 0;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";

// Components
import Bloc from "@/components/project/profile/blocs/Bloc.vue";
import AgentRow from "./AgentRow.vue";

export default {
    components: {
        Bloc,
        AgentRow,
    },

    data() {
        return {
            error: "",
        };
    },

    computed: {
        ...mapGetters(["project", "agents", "agentsLoaded"]),

        loaded() {
            return this.agentsLoaded;
        },

        // The agents of the user that work in this project: the ones for every
        // project and the ones bound to this one
        projectAgents() {
            return this.agents.filter(
                (agent) => !agent.project || agent.project.id === this.project.id
            );
        },
    },

    async created() {
        try {
            await store.dispatch("FETCH_AGENTS");
        } catch (e) {
            this.error = this.$t("agent.error");
        }
    },

    methods: {
        /**
         * Add an agent
         * See: @/components/agent/FormModal.vue
         */
        addAgent() {
            store.commit("SET_AGENT_EDITING", null);
            store.commit(OPEN_MODAL, "agent-form");
        },
    },
};
</script>
