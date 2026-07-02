<template>
    <item :tag="permission.sub ? 'div' : 'label'">
        <icon :class="['fa', permission.icon]" />
        <div class="hc-item-main-content" v-text="permission.name"></div>
        <icon
            v-if="permission.sub || permission.action"
            class="fa fa-caret-right"
        />
        <checkbox
            v-else
            :model-value="value"
            :disabled="disabled"
            @change="change"
        />
    </item>
</template>

<script>
import store from "@/store";

// Actions
import {
    ADD_ROLE_PERMISSION,
    REMOVE_ROLE_PERMISSION,
} from "@/actions/project/role/permission";

export default {
    props: {
        permission: {
            type: Object,
        },

        permissionValue: {
            type: Object,
        },

        value: {
            type: Boolean,
            default: false,
        },

        disabled: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {};
    },

    methods: {
        change(event) {
            store.dispatch(
                event.target.checked
                    ? ADD_ROLE_PERMISSION
                    : REMOVE_ROLE_PERMISSION,
                this.permissionValue
            );
            this.$emit("change", event);
        },
    },
};
</script>
