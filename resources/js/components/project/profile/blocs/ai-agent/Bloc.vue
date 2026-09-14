<template>
    <bloc icon="fa fa-robot" name="Agents IA">
        <template #options>
            <icon tag="a" class="fa fa-plus" @click.prevent.stop="openAgents" />
        </template>
        <template #body>
            <div class="hc-ai-agent-summary" @click="openAgents">
                <strong>{{ count }}</strong>
                <span>{{ count === 1 ? "agent configuré" : "agents configurés" }}</span>
            </div>
        </template>
    </bloc>
</template>

<style scoped>
.hc-ai-agent-summary { display: flex; gap: 8px; align-items: baseline; padding: 14px; cursor: pointer; }
.hc-ai-agent-summary strong { font-size: 22px; color: #1e6ee5; }
.hc-ai-agent-summary span { color: #6b7280; font-size: 13px; }
</style>

<script>
import Bloc from "@/components/project/profile/blocs/Bloc.vue";
import AiAgentService from "@/apis/project/ai-agent";

export default {
    components: { Bloc },
    data() { return { count: 0 }; },
    created() {
        console.log("[AI Agent UI] Comptage des agents du projet", this.project.slug);
        AiAgentService.index(this.project.slug).then(({ data }) => { this.count = data.length; }).catch((error) => console.error("[AI Agent UI] Échec du comptage", error));
    },
    computed: {
        project() { return this.$store.getters.project; },
    },
    methods: {
        openAgents() { this.$router.push({ name: "project.ai-agent", params: { project: this.project.slug } }); },
    },
};
</script>
