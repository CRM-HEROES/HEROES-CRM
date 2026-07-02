<template>
    <!-- Import tab -->
    <tab :count="tabsCount" :tab="tab" @tab:change="(t) => (tab = t)">
        <template #1>Prospects</template>
        <template #2>Messages</template>
        <template #3>Fichiers</template>
        <template #4>RDV</template>
        <template #5>Devis</template>
        <template #6>Filtres</template>
    </tab>

    <tab-layout :count="tabsCount" :tab="tab" class="hc-flex-1">
        <template #1>
            <prospect-layout />
        </template>
        <template #2>
            <thread-layout />
        </template>
        <template #3>
            <folder-layout />
        </template>
        <template #4>
            <calendar-layout />
        </template>
        <template #5>
            <order-layout />
        </template>
        <template #6>
            <label-layout />
        </template>
    </tab-layout>
</template>

<script>
// Components
import ProspectLayout from "./prospect/Layout.vue";
import LabelLayout from "./label/Layout.vue";
import ThreadLayout from "./thread/Layout.vue";
import FolderLayout from "./folder/Layout.vue";
import CalendarLayout from "./calendar/Layout.vue";
import OrderLayout from "./order/Layout.vue";

export default {
    components: {
        ProspectLayout,
        LabelLayout,
        ThreadLayout,
        FolderLayout,
        CalendarLayout,
        OrderLayout,
    },

    data() {
        return {
            tab: 0,
            tabsCount: 6,
        };
    },
};
</script>
