<template>
    <div class="hc-table" ref="wrapper">
        <table>
            <slot name="header"></slot>

            <tbody>
                <tr :style="{ height: `${firstRowHeight}px` }"></tr>
                <KeepAlive>
                    <slot name="body" :items="renderedItems"></slot>
                </KeepAlive>

                <tr :style="{ height: `${lastRowHeight}px` }"></tr>
            </tbody>
        </table>
    </div>
</template>

<style></style>

<script>
export default {
    props: {
        margin: {
            type: Number,
            default: 50,
        },
        items: {
            type: Array,
        },
        itemHeight: {
            type: Number,
        },
    },

    data() {
        return {
            startIndex: 0,
            step: 1,
            firstRowHeight: 0,
            lastRowHeight: 0,
        };
    },

    mounted() {
        this.handleScroll();
        this.$refs.wrapper.addEventListener("scroll", this.handleScroll);
    },

    destroyed() {
        this.$refs.wrapper.removeEventListener("scroll", this.handleScroll);
    },

    methods: {
        handleScroll() {
            const tableTop = this.$refs.wrapper.scrollTop;
            const viewPortY = this.$refs.wrapper.getBoundingClientRect().height;

            this.step = Math.min(
                this.items.length,
                Math.floor(viewPortY / this.itemHeight) + this.margin * 2
            );

            this.startIndex = Math.max(
                0,
                Math.min(
                    Math.floor(tableTop / this.itemHeight) - this.margin,
                    this.items.length - this.step
                )
            );

            this.firstRowHeight = this.startIndex * this.itemHeight;
            this.lastRowHeight =
                (this.items.length - this.startIndex - this.step) *
                this.itemHeight;
        },
    },

    watch: {
        items() {
            this.handleScroll();
        },
    },

    computed: {
        renderedItems() {
            return this.items.slice(
                this.startIndex,
                this.startIndex + this.step
            );
        },
    },
};
</script>
