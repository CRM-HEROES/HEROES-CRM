<template>
    <item tag="label">
        <icon class="fa fa-robot" />
        <div class="hc-item-main-content" v-text="agent.name"></div>
        <checkbox :model-value="isChecked" @change="toggleRelation" />
    </item>
</template>
<script>
import store from "@/store";
import { ADD_IMPORT_AI_AGENT, REMOVE_IMPORT_AI_AGENT } from "@/actions/project/import";

export default {
    props: {
        agent: {
            type: Object,
        },
        isChecked: {
            type: Boolean,
        },
    },
    methods: {
        toggleRelation(e) {
            if (e.target.checked) {
                store.dispatch(ADD_IMPORT_AI_AGENT, this.agent.id);
            } else {
                store.dispatch(REMOVE_IMPORT_AI_AGENT, this.agent.id);
            }
        },
    },
};
</script>
