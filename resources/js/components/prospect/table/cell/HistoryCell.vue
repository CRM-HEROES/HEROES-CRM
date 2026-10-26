<template>
    <div
        class="hc-history-cell"
        :tabindex="historyCount ? 0 : null"
        @mouseenter="hovered = true"
        @mouseleave="hovered = false"
        @focusin="focused = true"
        @focusout="leaveFocus"
        @keydown.esc.stop="close"
    >
        <span
            v-if="historyCount > 1 && !expanded"
            class="hc-history-cell-count"
            v-text="historyCount"
        ></span>
        <div v-if="latest" class="hc-history-cell-resume">
            <b v-text="latestDate"></b>
            <history-items :items="[latest]" />
        </div>
        <div
            v-if="expanded && historyCount"
            class="hc-history-cell-details"
            :aria-busy="loading"
            @click.stop
        >
            <div v-if="loading" class="hc-history-cell-loading" role="status">
                <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
                <span v-text="$t('prospect.table.cell.history.loading')"></span>
            </div>
            <div v-else-if="loadError" class="hc-history-cell-error" role="alert">
                <span v-text="$t('prospect.table.cell.history.error')"></span>
                <button
                    type="button"
                    class="hc-history-cell-retry"
                    @click.stop="loadHistory"
                    v-text="$t('prospect.table.cell.history.retry')"
                ></button>
            </div>
            <history-items v-else-if="histories.length" :items="histories" />
            <span v-else v-text="$t('prospect.table.cell.history.empty')"></span>
        </div>
    </div>
</template>

<script>
import dayjs from "dayjs";
import { mapGetters } from "vuex";
import ProspectService from "@/apis/project/prospect";
import HistoryItems from "@/components/prospect/profile/blocs/history/Items.vue";

export default {
    components: { HistoryItems },

    props: {
        prospect: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            hovered: false,
            focused: false,
            cachedHistory: [],
            loadedKey: null,
            pendingKey: null,
            requestId: 0,
            loading: false,
            loadError: false,
        };
    },

    watch: {
        expanded(expanded) {
            if (expanded) this.loadHistory();
        },

        summaryKey() {
            this.requestId++;
            this.cachedHistory = [];
            this.loadedKey = null;
            this.pendingKey = null;
            this.loading = false;
            this.loadError = false;
            if (this.expanded) this.loadHistory();
        },
    },

    beforeUnmount() {
        this.requestId++;
    },

    methods: {
        async loadHistory() {
            const key = this.summaryKey;
            if (
                !this.historyCount ||
                !this.project ||
                !this.project.slug ||
                !this.prospect.id ||
                this.loadedKey === key ||
                this.pendingKey === key
            ) {
                return;
            }

            const requestId = ++this.requestId;
            this.pendingKey = key;
            this.loading = true;
            this.loadError = false;

            try {
                const { data } = await ProspectService.history(
                    this.project.slug,
                    this.prospect.id
                );
                if (requestId !== this.requestId || key !== this.summaryKey) return;
                if (!Array.isArray(data)) throw new Error("Invalid prospect history response");
                this.cachedHistory = data;
                this.loadedKey = key;
            } catch (error) {
                if (requestId === this.requestId && key === this.summaryKey) {
                    this.loadError = true;
                }
            } finally {
                if (requestId === this.requestId && key === this.summaryKey) {
                    this.loading = false;
                    this.pendingKey = null;
                }
            }
        },

        close() {
            this.hovered = false;
            this.focused = false;
            this.$el.blur();
        },

        leaveFocus(event) {
            this.focused = this.$el.contains(event.relatedTarget);
        },
    },

    computed: {
        ...mapGetters(["project"]),

        latest() {
            const latest = this.prospect.history_latest;
            return latest && latest.created_at ? latest : null;
        },

        historyCount() {
            return Math.max(0, Number(this.prospect.history_count) || 0);
        },

        summaryKey() {
            return JSON.stringify([
                this.project && this.project.slug,
                this.prospect.id,
                this.historyCount,
                this.latest,
            ]);
        },

        histories() {
            return this.cachedHistory
                .filter((item) => item.created_at)
                .slice()
                .sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        },

        latestDate() {
            return this.latest
                ? dayjs(this.latest.created_at).format("DD/MM")
                : "";
        },

        expanded() {
            return this.hovered || this.focused;
        },
    },
};
</script>

<style>
.hc-history-cell {
    position: relative;
    width: 100%;
    height: 100%;
    min-height: 21px;
    font-size: 12px;
}

.hc-history-cell-resume {
    display: flex;
    align-items: center;
    gap: 4px;
    height: 21px;
    padding: 0 4px;
    overflow: hidden;
    white-space: nowrap;
    outline: 1px solid #e5e5e5;
}

.hc-history-cell-resume > b {
    flex-shrink: 0;
    font-size: 11px;
}

.hc-history-cell-resume > .hc-prospect-history-items {
    min-width: 0;
    flex: 1;
    overflow: hidden;
}

.hc-history-cell-resume .hc-prospect-profile-history-row {
    display: block;
    border: none;
    margin: 0;
    padding: 0;
}

.hc-history-cell-resume .hc-prospect-profile-history-row-icon,
.hc-history-cell-resume .hc-prospect-profile-history-row-footer {
    display: none;
}

.hc-history-cell-resume .hc-prospect-profile-history-row-body {
    overflow: hidden;
    text-overflow: ellipsis;
}

.hc-history-cell-resume .hc-prospect-profile-history-row-body * {
    display: inline;
    margin: 0 4px 0 0;
}

.hc-history-cell-resume .hc-prospect-profile-history-row-body br {
    display: none;
}

.hc-history-cell-resume .hc-prospect-profile-history-row-body .hc-prospect-profile-history-file-thumbnail {
    display: none;
}

.hc-history-cell-count {
    position: absolute;
    top: -7px;
    right: -7px;
    z-index: 1;
    padding: 0 5px;
    border-radius: 50%;
    background: #7939b8;
    color: white;
    font-size: 10px;
}

.hc-history-cell-details {
    position: absolute;
    top: 0;
    left: 0;
    z-index: 9000;
    width: 350px;
    min-width: 100%;
    max-width: 80vw;
    max-height: 400px;
    overflow-y: auto;
    overflow-x: hidden;
    overscroll-behavior: contain;
    box-sizing: border-box;
    padding: 16px;
    background: white;
    color: #222;
    white-space: normal;
    overflow-wrap: anywhere;
    box-shadow: 0 5px 20px -10px #0007;
    outline: 1px solid #ddd;
    border-radius: 0 7px 7px 7px;
}

.hc-history-cell-details .hc-prospect-profile-history-row {
    width: auto;
    min-width: 0;
}

.hc-history-cell-details .hc-prospect-profile-history-row-body {
    min-width: 0;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
}

.hc-history-cell-details .hc-prospect-profile-history-revision {
    flex-wrap: wrap;
}

.hc-history-cell-details .hc-prospect-profile-history-revision > div {
    min-width: 0;
    max-width: 100%;
}

.hc-history-cell-loading {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    text-align: center;
    padding: 8px;
}

.hc-history-cell-error {
    display: flex;
    align-items: center;
    gap: 8px;
}
</style>
