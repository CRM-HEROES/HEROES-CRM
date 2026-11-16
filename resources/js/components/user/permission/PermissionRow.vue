<template>
    <item :tag="permission.sub ? 'div' : 'label'" v-tooltip="permission.name">
        <icon v-if="permission.iconImage">
            <img
                :src="permission.iconImage"
                alt=""
                style="width: 22px; height: 22px; object-fit: contain"
            />
        </icon>
        <icon v-else :class="['fa', permission.icon]" />
        <div class="hc-item-main-content" v-text="permission.name"></div>
        <div v-if="count" class="hc-item-count" v-text="count"></div>
        <checkbox
            v-if="!permission.action"
            :model-value="value"
            :disabled="disabled"
            @change="change"
            @click.stop
        />
        <icon
            v-if="permission.sub || permission.action"
            class="fa fa-caret-right"
        />
    </item>
</template>

<script>
import store from "@/store";

// Actions
import {
    ADD_USER_PERMISSION,
    REMOVE_USER_PERMISSION,
} from "@/actions/project/user/permission";

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

        count: {
            type: Number,
            default: 0,
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
            if (this.permission && this.permission.bulkPermissionAction) {
                this.$emit("change", event);
                return;
            }

            store.dispatch(
                event.target.checked
                    ? ADD_USER_PERMISSION
                    : REMOVE_USER_PERMISSION,
                this.permissionValue
            );
            this.$emit("change", event);
        },
    },
};
</script>
