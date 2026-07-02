<template>
    <div :class="treeLayoutClass" ref="layout">
        <div class="tree-layout-wrapper" ref="wrapper">
            <div class="tree-layout-content" ref="content">
                <div
                    class="tree-layout-header"
                    ref="header"
                    @click="toggleOpen"
                >
                    <slot name="header"></slot>
                </div>
                <div class="tree-layout-body" :style="treeLayoutBodyStyle">
                    <slot name="body"></slot>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.tree-layout {
    width: 100%;
}

.tree-layout-wrapper {
    overflow: hidden;
    transition: height 0.2s cubic-bezier(0.23, 1, 0.32, 1);
    width: 100%;
}

.tree-layout-content {
    display: flex;
    flex-direction: column;
    width: 100%;
}

.tree-layout-header {
    cursor: pointer;
}

.tree-layout
    > .tree-layout-wrapper
    > .tree-layout-content
    > .tree-layout-header
    .fa-caret-down {
    transition: transform 200ms ease-out;
}

.tree-layout-open
    > .tree-layout-wrapper
    > .tree-layout-content
    > .tree-layout-header
    .fa-caret-down {
    transform: rotate(180deg);
}
</style>

<script>
export default {
    props: {
        tabulation: {
            type: Number,
            default: 0,
        },
        open: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            isOpen: this.open,
        };
    },

    mounted() {
        if (this.isOpen) {
            this.calcOpenedLayoutHeight();
        } else {
            this.$refs.wrapper.style.height =
                this.$refs.header.offsetHeight + "px";
        }
    },

    computed: {
        treeLayoutClass: function () {
            return [
                "tree-layout",
                "tree-layout-" + (this.isOpen ? "open" : "close"),
            ];
        },

        treeLayoutBodyStyle: function () {
            return {
                "padding-left": this.tabulation + "px",
            };
        },
    },

    methods: {
        /**
         * toggle open value
         */
        toggleOpen() {
            this.isOpen = !this.isOpen;
        },

        /**
         * Opened layout height
         */
        calcOpenedLayoutHeight() {
            setTimeout(() => {
                if (this.isOpen) {
                    this.$refs.wrapper.style.height = "auto";
                }
            }, 300);
            this.$refs.wrapper.style.height =
                this.$refs.content.getBoundingClientRect().height + "px";
        },

        /**
         * Closed layout height
         */
        calcClosedLayoutHeight() {
            this.$refs.wrapper.style.height =
                this.$refs.wrapper.getBoundingClientRect().height + "px";
            this.$refs.wrapper.style.height =
                this.$refs.header.getBoundingClientRect().height + "px";
        },

        /**
         * Layout height
         */
        calcLayoutHeight() {
            if (this.isOpen) {
                this.calcOpenedLayoutHeight();
            } else {
                this.calcClosedLayoutHeight();
            }
        },
    },

    watch: {
        isOpen: function (newValue) {
            if (newValue) {
                this.$emit("open");
            } else {
                this.$emit("closed");
            }
            this.calcLayoutHeight();
        },
        open: function (newValue) {
            this.isOpen = newValue;
        },
    },
};
</script>
