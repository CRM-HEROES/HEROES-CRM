<template>
    <item tag="label">
        <icon class="fa fa-id-badge" />
        <div class="hc-item-main-content" v-text="role.name"></div>
        <checkbox :model-value="isChecked" @change="toggleRelation" />
    </item>
</template>
<script>
import store from "@/store";
import { ADD_IMPORT_ROLE, REMOVE_IMPORT_ROLE } from "@/actions/project/import";

export default {
    props: {
        role: {
            type: Object,
        },
        isChecked: {
            type: Boolean,
        },
    },
    methods: {
        toggleRelation(e) {
            if (e.target.checked) {
                store.dispatch(ADD_IMPORT_ROLE, this.role.id);
            } else {
                store.dispatch(REMOVE_IMPORT_ROLE, this.role.id);
            }
        },
    },
};
</script>
