<template>
    <div
        :class="[
            'hc-slide',
            open ? 'active' : '',
            leftSlideOpen(name) ? 'left' : '',
        ]"
        @click.stop
    >
        <template v-if="firstOpen">
            <div class="hc-slide-header">
                <icon
                    v-if="icon"
                    :class="['hc-slide-icon', icon]"
                    :size="40"
                ></icon>
                <router-link
                    class="hc-slide-title"
                    v-if="url"
                    :to="url"
                    v-text="title"
                ></router-link>
                <span v-else class="hc-slide-title" v-text="title"></span>
                <div
                    class="hc-slide-close hc-flex-column hc-flex-center"
                    @click="close"
                >
                    &times;
                </div>
            </div>
            <item tag="a" @click="close" class="bordered" v-if="subSlideOpen">
                <icon class="fa fa-caret-left" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('back')"
                    style="color: rgb(0, 111, 202)"
                ></div>
            </item>
            <div class="hc-slide-body">
                <slot></slot>
            </div>
        </template>
    </div>
</template>

<style>
.hc-slide {
    background-color: #fff;
    border-left: 1px solid #e5e5e5;
    display: flex;
    flex-direction: column;
    height: 100%;
    max-width: 100%;
    overflow: hidden;
    position: fixed;
    right: 0;
    top: 0;
    transform: translateX(100%);
    transition: all 0.2s cubic-bezier(0.23, 1, 0.32, 1);
    width: 400px;
    z-index: 10;
}

.hc-slide.left {
    right: unset;
    left: 0;
    transform: translateX(-100%);
    border-left: unset;
    border-right: 1px solid #e5e5e5;
}

.hc-slide.active {
    transform: translateX(0%);
    box-shadow: 0 0 50px rgba(0, 0, 0, 0.05);
    transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
}

.hc-slide-header {
    border-bottom: 1px solid #eeeeee;
    display: flex;
    flex-direction: row;
    font-size: 13px;
    font-weight: bold;
    align-items: center;
}

.hc-slide-close {
    border-radius: 15px;
    color: #888;
    cursor: pointer;
    font-size: 18px;
    font-weight: 600;
    height: 30px;
    margin: 5px;
    text-align: center;
    width: 30px;
}

.hc-slide-close:hover {
    background-color: #eeeeee;
    color: #555555;
}

.hc-slide-title {
    flex: 1;
    font-size: 12px;
    font-weight: 600;
    line-height: 18px;
    padding: 10px 10px;
    text-align: center;
    text-decoration: none;
    color: #333333;
}

.hc-slide-icon + .hc-slide-title {
    padding-left: 5px;
}

.hc-slide-body {
    display: flex;
    flex: 1;
    flex-direction: column;
    overflow: auto;
    text-align: left;
}

.active .hc-slide-body {
    display: flex;
}

.dark .hc-slide {
    background-color: #3a3c3a;
    border-left-color: #444444;
}

.dark .hc-slide-header {
    border-bottom-color: #444444;
}

.dark .hc-slide-title {
    color: #bbbbbb;
}

.dark .hc-slide-close:hover {
    background-color: #444444;
    color: #999999;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { CLOSE_SLIDE, OPEN_SLIDE } from "@/actions/slide";

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
        icon: {
            type: String,
            default: "",
        },

        /**
         *
         */
        url: {
            type: String,
            default: null,
        },

        /**
         *
         */
        left: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            firstOpen: false,
        };
    },

    methods: {
        /**
         * Close slide
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
            if (newValue && !this.firstOpen) {
                this.firstOpen = true;
                await this.$nextTick();
                this.$emit("first-open");
            }

            this.$emit(newValue ? "open" : "closed");
        },
    },

    computed: {
        ...mapGetters(["slideOpen", "leftSlideOpen", "subSlideOpen"]),

        /**
         *
         */
        open: {
            get() {
                return (
                    this.slideOpen(this.name) || this.leftSlideOpen(this.name)
                );
            },
            set(value) {
                if (!value) {
                    store.commit(CLOSE_SLIDE, this.leftSlideOpen(this.name));
                } else {
                    store.commit(OPEN_SLIDE, this.name);
                }
            },
        },
    },
};
</script>
