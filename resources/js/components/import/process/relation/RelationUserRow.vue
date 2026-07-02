<template>
    <item tag="label">
        <icon class="fa fa-tags" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <checkbox :model-value="isChecked" @change="toggleRelation" />
    </item>
</template>

<script>
import store from "@/store";

// Actions
import { ADD_IMPORT_USER, REMOVE_IMPORT_USER } from "@/actions/project/import";

export default {
    props: {
        user: {
            type: Object,
        },
        isChecked: {
            type: Boolean,
        },
    },

    methods: {
        toggleRelation(e) {
            if (e.target.checked) {
                store.dispatch(ADD_IMPORT_USER, this.user.id);
            } else {
                store.dispatch(REMOVE_IMPORT_USER, this.user.id);
            }
        },
    },
};
</script>
