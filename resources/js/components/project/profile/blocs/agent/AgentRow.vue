<template>
    <item @click.prevent.stop="edit">
        <img
            v-if="agent.avatar_url"
            class="hc-agent-row-avatar"
            :src="agent.avatar_url"
            :alt="agent.name"
            width="30"
            height="30"
        />
        <icon v-else class="fa fa-robot" :size="30" />
        <div class="hc-item-main-content hc-flex-column">
            <div v-text="agent.name"></div>
            <div class="hc-agent-row-detail" v-text="detail"></div>
        </div>
        <span
            class="hc-agent-row-autonomy"
            :class="`is-${agent.autonomy}`"
            v-text="$t(`agent.autonomy_short.${agent.autonomy}`)"
        ></span>
        <icon
            tag="a"
            class="fa fa-cog"
            :title="$t('agent.edit')"
            @click.prevent.stop="edit"
        />
    </item>
</template>

<style>
.hc-agent-row-avatar {
    width: 30px;
    height: 30px;
    flex: 0 0 30px;
    border-radius: 50%;
    object-fit: cover;
}

.hc-agent-row-detail {
    color: #777;
    font-size: 11px;
}

.hc-agent-row-autonomy {
    font-size: 10px;
    padding: 1px 6px;
    border-radius: 8px;
    background: #eee;
    white-space: nowrap;
}

.hc-agent-row-autonomy.is-auto {
    background: #fde7c8;
}

.hc-agent-row-autonomy.is-propose {
    background: #dbeafe;
}
</style>

<script>
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";

export default {
    props: {
        agent: {
            type: Object,
            required: true,
        },
    },

    computed: {
        detail() {
            return [
                this.agent.specialty,
                this.agent.project ? this.agent.project.name : this.$t("agent.all_projects"),
            ]
                .filter(Boolean)
                .join(" · ");
        },
    },

    methods: {
        // Open the form on this agent
        edit() {
            store.commit("SET_AGENT_EDITING", this.agent);
            store.commit(OPEN_MODAL, "agent-form");
        },
    },
};
</script>
