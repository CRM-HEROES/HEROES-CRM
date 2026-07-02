<template>
    <div
        :class="[
            'hc-tabs',
            position == 'bottom' ? 'hc-tabs-bottom' : '',
            movingLeft ? 'hc-tabs-moving-left' : '',
        ]"
        :style="style"
        ref="tabs"
    >
        <div ref="tabsContainer">
            <div
                v-for="i in count"
                :class="[i - 1 == currentTab ? 'active' : '']"
                @click="setTab(i - 1)"
            >
                <slot :name="i"></slot>
            </div>

            <span class="hc-tabs-indicator" :style="indicatorStyle"></span>
        </div>
    </div>
</template>

<style>
.hc-tabs {
    width: 100%;
    position: relative;
    display: flex;
    flex-direction: row;
    justify-content: center;
}

.hc-tabs:before {
    content: "";
    width: 100%;
    height: 2px;
    bottom: 0;
    left: 0;
    position: absolute;
    z-index: 1;
    background-color: #eeeeee;
}

.hc-tabs.hc-tabs-bottom:before {
    top: 0;
    bottom: unset;
}

.hc-tabs > div::-webkit-scrollbar {
    display: none;
}

.hc-tabs > div {
    max-width: 100%;
    height: 100%;
    font-size: 0;
    position: relative;
    display: flex;
    flex-direction: row;
    overflow-y: hidden;
    scroll-behavior: smooth;
}

.hc-tabs > div > div {
    font-size: 12px;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 0 10px;
    cursor: pointer;
    color: #777777;
    white-space: nowrap;
}

.hc-tabs > div > div:hover {
    background-color: #fafafa;
}

.hc-tabs > div > div.active {
    background-color: #f5f5f5;
    color: #000000;
}

.hc-tabs-indicator {
    position: absolute;
    z-index: 2;
    bottom: 0;
    height: 3px;
    background-color: #7939b8;
    border-radius: 2px;
    transition: left 0.35s cubic-bezier(0.23, 1, 0.32, 1) 0.05s,
        right 0.45s cubic-bezier(0.23, 1, 0.32, 1) 0.1s;
}

.hc-tabs-moving-left .hc-tabs-indicator {
    transition: left 0.45s cubic-bezier(0.23, 1, 0.32, 1) 0.1s,
        right 0.35s cubic-bezier(0.23, 1, 0.32, 1) 0.05s;
}

.hc-tabs.hc-tabs-bottom .hc-tabs-indicator {
    top: 0;
    bottom: unset;
}

.dark .hc-tabs:before {
    background-color: #555555;
}

.dark .hc-tabs > div > div {
    color: #aaaaaa;
}

.dark .hc-tabs > div > div:hover {
    background-color: #373737;
}

.dark .hc-tabs > div > div.active {
    background-color: #333333;
    color: #dddddd;
}

.dark .hc-tabs-indicator {
    background-color: #a46dd9;
}
</style>

<script>
export default {
    props: {
        count: {
            type: Number,
            default: 1,
        },
        tab: {
            type: Number,
            default: 0,
        },
        position: {
            type: String,
            default: "top",
        },
        height: {
            type: String,
            default: "38px",
        },
    },

    data() {
        return {
            currentTab: -1,
            movingLeft: true,
        };
    },

    mounted() {
        this.setTab(this.tab);
    },

    methods: {
        setTab(tab) {
            this.currentTab = tab;
        },
    },

    watch: {
        tab(newValue) {
            this.currentTab = newValue;
        },

        currentTab(newValue, oldValue) {
            if (oldValue == -1) {
                return;
            }

            this.movingLeft = oldValue < newValue;

            const tabsContainer = this.$refs.tabsContainer;
            if (tabsContainer) {
                tabsContainer.children[newValue].scrollIntoView();
            }

            this.$emit("tab:change", newValue, oldValue);
        },
    },

    computed: {
        /**
         */
        style() {
            return {
                height: this.height,
            };
        },

        /**
         */
        indicatorStyle() {
            if (this.currentTab < 0) {
                return {};
            }

            const tabsContainer = this.$refs.tabsContainer;
            if (!tabsContainer) {
                return {};
            }

            const currentTab = tabsContainer.children[this.currentTab];

            return {
                left: `${currentTab.offsetLeft + 10}px`,
                right: `${
                    tabsContainer.offsetWidth -
                    currentTab.offsetLeft -
                    currentTab.offsetWidth +
                    10
                }px`,
            };
        },
    },
};
</script>
