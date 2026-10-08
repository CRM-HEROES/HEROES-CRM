<template>
    <div class="hc-flex-column" style="height: 100%">
        <item @click="$emit('back')" class="bordered">
            <icon class="fa fa-caret-left" />
            <div
                class="hc-item-main-content"
                v-text="$t('prospect.interaction.call_by_kavkom')"
            ></div>
        </item>
        <div class="hc-kavkom-call-panel">
            <div class="hc-kavkom-call-card">
                <div class="hc-kavkom-call-card-header">
                    <span class="hc-kavkom-call-icon">
                        <icon class="fa fa-phone" />
                    </span>
                    <div>
                        <div class="hc-kavkom-call-label">Appel Kavkom</div>
                        <div
                            class="hc-kavkom-call-number"
                            v-text="number"
                        ></div>
                    </div>
                    <span
                        :class="[
                            'hc-kavkom-call-ready',
                            ready ? 'is-ready' : 'is-loading',
                        ]"
                    >
                        <i class="fa fa-circle"></i>
                        {{ ready ? "Prêt" : "Connexion" }}
                    </span>
                </div>

                <!--
                    The softphone registration is shared by the whole session
                    (see @/utils/kavkom-phone): this panel only displays its
                    state and auto-answers the agent leg sent by the PBX.
                -->
                <kavkom id="kavkom-webphone" :project-id="projectId" />
            </div>

            <div
                v-if="message"
                :class="['hc-kavkom-call-status', success ? 'success' : 'error']"
                v-text="message"
            ></div>

            <button
                type="button"
                class="hc-button-secondary hc-kavkom-call-action"
                :disabled="calling || !ready"
                @click="$emit('call')"
            >
                <i class="fa fa-phone"></i>
                {{ calling ? "Appel en cours..." : "Appeler" }}
            </button>
        </div>
    </div>
</template>

<style>
.hc-kavkom-call-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 14px;
    padding: 18px;
    text-align: center;
    background: linear-gradient(160deg, #faf7ff 0%, #ffffff 55%);
}
.hc-kavkom-call-card {
    width: 100%;
    padding: 16px;
    text-align: left;
    background: #fff;
    border: 1px solid #eadcf7;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(116, 52, 162, 0.08);
}
.hc-kavkom-call-card-header {
    display: flex;
    align-items: center;
    gap: 11px;
    margin-bottom: 14px;
}
.hc-kavkom-call-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    color: #fff;
    background: #8e24aa;
    border-radius: 10px;
}
.hc-kavkom-call-label {
    color: #7b7284;
    font-size: 12px;
    font-weight: 600;
}
.hc-kavkom-call-number {
    margin-top: 2px;
    font-size: 18px;
    font-weight: 600;
    color: #343a40;
}
.hc-kavkom-call-ready {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-left: auto;
    padding: 4px 7px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}
.hc-kavkom-call-ready i {
    font-size: 7px;
}
.hc-kavkom-call-ready.is-ready {
    color: #16794a;
    background: #e7f7ef;
}
.hc-kavkom-call-ready.is-loading {
    color: #896b16;
    background: #fff6d8;
}
.hc-kavkom-call-status {
    font-size: 13px;
    color: #6c757d;
    pointer-events: none;
}
.hc-kavkom-call-status.success {
    color: #2e7d32;
}
.hc-kavkom-call-status.error {
    color: #c62828;
}
.hc-kavkom-call-panel > .hc-button-secondary {
    width: 100%;
    min-height: 40px;
    color: #fff;
    background: #8e24aa;
    border-color: #8e24aa;
}
.hc-kavkom-call-action {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.hc-kavkom-call-panel > .hc-button-secondary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>

<script>
import Kavkom from "@/components/utils/Kavkom.vue";

export default {
    components: {
        Kavkom,
    },

    props: {
        number: {
            type: String,
            default: "",
        },

        projectId: {
            type: [Number, String],
            default: null,
        },

        ready: {
            type: Boolean,
            default: false,
        },

        calling: {
            type: Boolean,
            default: false,
        },

        message: {
            type: String,
            default: "",
        },

        success: {
            type: Boolean,
            default: false,
        },
    },

    emits: ["back", "call"],
};
</script>
