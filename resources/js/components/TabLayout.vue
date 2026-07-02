<template>
    <div class="tab-layout tab-layout-slide">
        <div :style="style">
            <div v-for="i in count" :style="childStyle">
                <div
                    v-if="isTabDisplayed(i - 1)"
                    :style="{ display: i - 1 == tab ? 'block' : 'none' }"
                >
                    <slot :name="i"></slot>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.tab-layout {
    width: 100%;
    height: 100%;
    overflow: hidden !important;
    display: flex;
    flex-direction: column;
}

.tab-layout > div {
    overflow: hidden;
    width: auto;
    height: 100%;
    white-space: nowrap;
    font-size: 0;
    max-height: 100%;
    transition: margin-left 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    display: flex;
    flex-direction: row;
}

.tab-layout > div > div {
    flex: 1;
    font-size: 12px;
    display: inline-block;
    max-height: 100%;
    overflow: hidden;
    white-space: normal;
}

.tab-layout > div > div > div {
    width: 100%;
    height: 100%;
    vertical-align: top;
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
    },

    data() {
        return {
            displayedTabs: [this.tab],
        };
    },

    methods: {
        isTabDisplayed(tab) {
            return this.displayedTabs.indexOf(tab) >= 0;
        },
    },

    computed: {
        style() {
            return {
                width: 100 * this.count + "%",
                marginLeft: -100 * this.tab + "%",
            };
        },
        childStyle() {
            return { width: 100 / this.count + "%" };
        },
    },

    watch: {
        tab(newValue, oldValue) {
            if (!this.isTabDisplayed(newValue)) {
                this.displayedTabs = [...this.displayedTabs, newValue];
            }

            this.$emit("tab:change", newValue, oldValue);
        },
    },
};
</script>
