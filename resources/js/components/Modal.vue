<template>
    <div :class="['hc-modal', open ? 'active' : '']" :style="overlayStyle">
        <div class="hc-modal-overlay" @click="close"></div>
        <div class="hc-modal-content" :style="style">
            <template v-if="firstOpen">
                <div class="hc-modal-header">
                    {{ title }}
                    <div class="hc-modal-close" @click="close">&times;</div>
                </div>
                <div class="hc-modal-body">
                    <slot></slot>
                </div>
            </template>
        </div>
    </div>
</template>

<style>
.hc-modal {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
    left: 0;
    opacity: 0;
    position: fixed;
    text-align: center;
    top: 0;
    -webkit-transition: all 100ms ease-out;
    transition: all 100ms ease-out;
    visibility: hidden;
    width: 100%;
    z-index: 1000;
    padding: 15px 0;
}

.hc-modal.active {
    opacity: 1;
    overflow: auto;
    visibility: visible;
}

.hc-modal-overlay {
    position: absolute;
    height: 100%;
    width: 100%;
    top: 0;
    left: 0;
    background-color: rgba(0, 0, 0, 0.4);
}

.hc-modal-content {
    position: relative;
    display: flex;
    flex-direction: column;
    background-color: #fff;
    border-radius: 5px;
    box-shadow: 0 5px 8px -5px #00000033;
    max-width: 95%;
    max-height: 100%;
    overflow: hidden;
    position: relative;
    transform: scale(0.9);
    transition: all 100ms ease-out;
    vertical-align: middle;
    width: 400px;
}

.hc-modal.active > .hc-modal-content {
    transform: scale(1);
}

.hc-modal-header {
    font-size: 13px;
    font-weight: bold;
    height: 50px;
    line-height: 50px;
    text-align: center;
    padding: 0 40px;
    white-space: nowrap;
    text-overflow: ellipsis;
    color: #333333;
}

.hc-modal-close {
    cursor: pointer;
    width: 30px;
    height: 30px;
    line-height: 30px;
    text-align: center;
    font-size: 19px;
    color: #aaaaaa;
    position: absolute;
    right: 10px;
    top: 10px;
    border-radius: 50%;
}

.hc-modal-close:hover {
    background-color: #eeeeee;
    color: #555555;
}

.hc-modal-close:hover {
    color: #555555;
}

.hc-modal-body {
    padding: 5px 10px;
    text-align: left;
    flex: 1;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.dark .hc-modal-header {
    color: #bbbbbb;
}

.dark .hc-modal {
    background-color: rgba(0, 0, 0, 0.6);
}

.dark .hc-modal-close:hover {
    background-color: #444444;
    color: #999999;
}

.dark .hc-modal-content {
    background-color: #2e302e;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import { OPEN_MODAL, CLOSE_MODAL } from "@/actions/modal";

export default {
    props: {
        /**
         *
         */
        name: {
            type: String,
            default: null,
        },

        /**
         *
         */
        title: {
            type: String,
            default: "",
        },

        /**
         *
         */
        width: {
            type: Number,
            default: null,
        },
    },

    data() {
        return {
            firstOpen: false,
        };
    },

    methods: {
        /**
         * Close modal
         */
        close() {
            this.open = false;
        },
    },

    watch: {
        /**
         *
         */
        async open(newValue) {
            this.$emit(newValue ? "open" : "closed");

            if (newValue && !this.firstOpen) {
                this.firstOpen = true;
                await this.$nextTick();
                this.$emit("first-open");
            }
        },
    },

    computed: {
        ...mapGetters(["modalOpen", "modalIndex"]),

        overlayStyle() {
            return {
                zIndex: this.modalOpen(this.name)
                    ? 100 + this.modalIndex(this.name)
                    : 1000,
            };
        },

        style() {
            const style = {};

            if (this.width) {
                style.width = `${this.width}px`;
            }

            return style;
        },

        /**
         *
         */
        open: {
            get() {
                return this.modalOpen(this.name);
            },
            set(value) {
                if (!value) {
                    store.commit(CLOSE_MODAL);
                } else {
                    store.commit(OPEN_MODAL, this.name);
                }
            },
        },
    },
};
</script>
