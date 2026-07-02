<template>
    <div class="frame-layout">
        <div>
            <template v-for="i in count">
                <div
                    v-if="isTabDisplayed(i - 1)"
                    :style="{ width: i == tab + 1 ? '100%' : '0' }"
                >
                    <slot :name="i"></slot>
                </div>
            </template>
        </div>
    </div>
</template>

<style>
.frame-layout {
    width: 100%;
    height: 100%;
    overflow: hidden !important;
}

.frame-layout > div {
    height: 100%;
    width: 100%;
}

.frame-layout > div > div {
    width: 100%;
    height: 100%;
    position: relative;
    float: left;
    overflow: hidden;
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

    watch: {
        tab: function (newValue, oldValue) {
            if (!this.isTabDisplayed(newValue)) {
                this.displayedTabs = [...this.displayedTabs, newValue];
            }

            this.$emit("tab-changed", newValue, oldValue);
        },
    },
};
</script>
