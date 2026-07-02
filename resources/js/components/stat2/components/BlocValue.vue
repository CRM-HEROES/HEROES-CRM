<template>
    <div
        class="hc-stat-value-bloc"
        :style="{ backgroundColor: bgcolor, color: color }"
    >
        <div class="hc-stat-value-bloc-icon">
            <i :class="icon"></i>
        </div>
        <div class="hc-stat-value-bloc-details">
            <div v-if="value" class="hc-stat-value-bloc-value">
                <span>{{ value }}</span
                >&nbsp;
                <span>{{ unity }}</span>
            </div>
            <div class="hc-stat-value-bloc-label">{{ label }}</div>
        </div>
        <a
            v-if="value"
            class="hc-stat-value-bloc-remove"
            @click.prevent="remove"
        >
            <i class="fa fa-times"></i>
        </a>
    </div>
</template>

<style scoped>
.hc-stat-value-bloc {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 160px;
    padding: 20px 0;
    border-radius: 8px;
    gap: 0px;
    position: relative;
}

.hc-stat-value-bloc-icon {
    font-size: 24px;
    color: #0007;
}

.hc-stat-value-bloc-details {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin-top: 8px;
    gap: 0px;
}

.hc-stat-value-bloc-value {
    font-size: 14px;
    font-weight: 700;
    line-height: 28px;
}

.hc-stat-value-bloc-label {
    font-size: 13px;
}

.hc-stat-value-bloc-remove {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 16px;
    color: #fff;
    background-color: #0002;
    cursor: pointer;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    opacity: 0;
    visibility: hidden;
    transform: scale(0.1);
    transition: all 0.1s ease-out;
    text-decoration: none;
}

.hc-stat-value-bloc:hover .hc-stat-value-bloc-remove {
    opacity: 1;
    visibility: visible;
    transform: scale(1);
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { UPDATE_PROJECT_USER_SETTING } from "@/actions/project/user/setting";

export default {
    props: {
        icon: String,
        id: String,
        label: String,
        value: {
            type: Number,
            default: null,
        },
        unity: String,
        bgcolor: String,
        color: String,
    },

    methods: {
        remove() {
            const newSettings = this.projectUserSettingsStatBlocValue.filter(
                (bloc) => bloc.key !== this.id
            );

            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "stat-bloc-value",
                value: newSettings,
            });
        },
    },

    computed: {
        ...mapGetters(["projectUserSettingsStatBlocValue"]),
    },
};
</script>
