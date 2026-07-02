<template>
    <div
        id="tutorial-tooltip"
        :class="[
            elementToShow ? 'shown' : '',
            confirmingDoNotShowAnymore ? 'confirming-do-not-show-anymore' : '',
        ]"
    >
        <div
            class="tutorial-tooltip-overlay"
            @click.stop="removeTutorials"
            :style="topOverlayStyle"
        ></div>
        <div
            class="tutorial-tooltip-overlay"
            @click.stop="removeTutorials"
            :style="rightOverlayStyle"
        ></div>
        <div
            class="tutorial-tooltip-overlay"
            @click.stop="removeTutorials"
            :style="bottomOverlayStyle"
        ></div>
        <div
            class="tutorial-tooltip-overlay"
            @click.stop="removeTutorials"
            :style="leftOverlayStyle"
        ></div>

        <div
            ref="tutorialTooltip"
            id="tutorial-tooltip-text"
            :style="style.element"
            @click.stop
        >
            <template v-if="elementToShow">
                <div
                    id="tutorial-tooltip-text-body"
                    v-html="elementToShow.html"
                ></div>
                <div id="tutorial-tooltip-text-footer">
                    <a
                        @click.prevent.stop="doNotShowAnymore"
                        style="
                            background: none;
                            color: #fffb;
                            font-size: 11px;
                            padding: 5px;
                        "
                        >Ne plus afficher</a
                    >
                    <a @click.prevent.stop="removeTutorials">OK</a>
                    <a
                        @click.prevent.stop="nextTooltip"
                        v-if="tutorials.length > 1"
                        >Suivant</a
                    >
                </div>
            </template>

            <div
                id="tutorial-tooltip-caret"
                :style="style.caret"
                :class="position"
            ></div>
        </div>
    </div>
</template>

<style>
#tutorial-tooltip {
    position: fixed;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    display: none;
    z-index: 1000;
    pointer-events: none;
}

#tutorial-tooltip.shown {
    display: block;
}

#tutorial-tooltip.confirming-do-not-show-anymore {
    visibility: hidden;
}

#tutorial-tooltip-text {
    pointer-events: all;
    position: fixed;
    width: 300px;
    height: auto;
    background: #7574ff;
    color: #ffffff;
    padding: 20px;
    font-size: 13px;
    line-height: 25px;
    border-radius: 5px;
    transition: all 0.2s cubic-bezier(0.23, 1, 0.32, 1);
}

#tutorial-tooltip-text-footer {
    padding-top: 13px;
    text-align: right;
}

#tutorial-tooltip-text-footer > a {
    display: inline-block;
    padding: 5px 10px;
    background: #ffffff15;
    border-radius: 3px;
    color: white;
    cursor: pointer;
    margin-left: 5px;
}

#tutorial-tooltip-text-footer > a:hover {
    text-decoration: none;
    background: #fff2;
}

#tutorial-tooltip-caret {
    position: absolute;
    transition: all 0.2s cubic-bezier(0.23, 1, 0.32, 1);
}

#tutorial-tooltip-caret:before {
    content: "";
    position: absolute;
    width: 0;
    height: 0;
}

#tutorial-tooltip-caret.left:before {
    top: -12px;
    border-left: none;
    border-top: 12px solid transparent;
    border-right: 12px solid #7574ff;
    border-bottom: 12px solid transparent;
}

#tutorial-tooltip-caret.top:before {
    left: -12px;
    border-left: 12px solid transparent;
    border-top: none;
    border-right: 12px solid transparent;
    border-bottom: 12px solid #7574ff;
}

#tutorial-tooltip-caret.right:before {
    top: -12px;
    border-left: 12px solid #7574ff;
    border-top: 12px solid transparent;
    border-right: none;
    border-bottom: 12px solid transparent;
}

#tutorial-tooltip-caret.bottom:before {
    left: -12px;
    border-left: 12px solid transparent;
    border-top: 12px solid #7574ff;
    border-right: 12px solid transparent;
    border-bottom: none;
}

.tutorial-tooltip-overlay {
    position: fixed;
    background: #7574ff11;
    transition: all 0.2s cubic-bezier(0.23, 1, 0.32, 1);
    backdrop-filter: blur(1px);
    pointer-events: all;
}
</style>

<script>
import EventBus from "@/utils/event-bus";

export default {
    props: [],

    data() {
        return {
            tutorials: [],
            cookieDoNotShowAnymore: "do-not-show-anymore",
            update: 0,
            confirmingDoNotShowAnymore: false,
        };
    },

    created() {
        EventBus.on("tuto", (element, html, key, name, timeout) => {
            this.tooltip(element, html, key, name, timeout);
        });
    },

    methods: {
        tooltip(element, html, key, name, timeout) {
            /*if (!this.showTuto) {
                return;
            }*/

            if (!key) {
                return;
            }

            if (this.cookieExists(this.cookieDoNotShowAnymore)) {
                return;
            }

            if (this.cookieExists(key)) {
                return;
            }

            if (!element) {
                return;
            }

            timeout = timeout !== undefined ? timeout : 0;

            this.tutorials.push({
                element: element,
                html: html,
                key: key,
                name: name,
                timeout: timeout,
            });

            this.currentTooltip();
        },

        nextTooltip() {
            this.tutorials.shift();
            this.currentTooltip();
        },

        currentTooltip() {
            const timeout =
                this.tutorials.length > 0 ? this.tutorials[0].timeout : 0;

            if (timeout == 0) {
                this.$nextTick(() => {
                    this.update++;
                });
            } else {
                setTimeout(() => {
                    this.tutorials = this.tutorials.map((tutorial, index) =>
                        index == 0 ? { ...tutorial, timeout: 0 } : tutorial
                    );
                    this.$nextTick(() => {
                        this.update++;
                    });
                }, timeout);
            }
        },

        removeTutorials() {
            this.tutorials = [];
        },

        doNotShowAnymore() {
            this.confirmingDoNotShowAnymore = true;

            hcConfirm(
                "Etes-vous sûr de ne plus afficher aucun tuto ?",
                async () => {
                    this.setCookie(this.cookieDoNotShowAnymore, 1);
                    this.removeTutorials();
                    this.confirmingDoNotShowAnymore = false;
                },
                () => {
                    this.confirmingDoNotShowAnymore = false;
                    this.update++;
                }
            );
        },

        setCookie(name, value) {
            let date = new Date();
            date.setFullYear(date.getFullYear() + 10);
            document.cookie =
                "hc-tuto." +
                name +
                "=" +
                value +
                "; expires=" +
                date.toGMTString() +
                "; path=/";
        },

        cookieExists(name) {
            return (
                Object.keys(
                    Object.fromEntries(
                        document.cookie.split("; ").map((c) => c.split("="))
                    )
                ).indexOf("hc-tuto." + name) !== -1
            );
        },
    },

    watch: {
        /**
         */
        elementToShow(newValue, oldValue) {
            if (!this.elementToShow) {
                return;
            }

            this.setCookie(this.elementToShow.key, this.elementToShow.name);
        },
    },

    computed: {
        showTuto() {
            return deviceType() == "desktop";
        },

        elementToShow() {
            return this.tutorials.length > 0 && this.tutorials[0].timeout == 0
                ? this.tutorials[0]
                : null;
        },

        position() {
            if (!this.elementToShow) {
                return "top";
            }

            this.update;

            const elementRect =
                this.elementToShow.element.getBoundingClientRect();
            const windowWidth = window.innerWidth;
            const windowHeight = window.innerHeight;
            const elementRectLeft = elementRect.left + elementRect.width / 2;
            const elementRectTop = elementRect.top + elementRect.height / 2;
            const A = (windowHeight / windowWidth) * elementRectLeft;
            const B =
                windowHeight - (windowHeight / windowWidth) * elementRectLeft;

            if (deviceType() == "desktop") {
                if (elementRectTop <= A && elementRectTop <= B) {
                    return "top";
                } else if (elementRectTop >= A && elementRectTop >= B) {
                    return "bottom";
                } else if (elementRectTop >= A && elementRectTop <= B) {
                    return "left";
                } else {
                    return "right";
                }
            } else {
                if (elementRectTop <= windowHeight / 2) {
                    return "top";
                } else {
                    return "bottom";
                }
            }
        },

        style() {
            if (!this.elementToShow) {
                return { element: {}, caret: {} };
            }

            this.update;

            let elementRect =
                this.elementToShow.element.getBoundingClientRect();
            const tooltipRect =
                this.$refs.tutorialTooltip.getBoundingClientRect();
            const windowWidth = window.innerWidth;
            const windowHeight = window.innerHeight;

            if (
                elementRect.left < 0 ||
                elementRect.left + elementRect.width > windowWidth ||
                elementRect.top < 0 ||
                elementRect.top + elementRect.height > windowHeight
            ) {
                this.elementToShow.element.scrollIntoView();
                elementRect =
                    this.elementToShow.element.getBoundingClientRect();
                this.update++;
            }

            if (this.position == "left") {
                let left = elementRect.left + elementRect.width + 12;
                let top =
                    elementRect.top +
                    (elementRect.height - tooltipRect.height) / 2;
                let right = "unset";
                let bottom = "unset";
                let caretLeft = -12;
                let caretTop = tooltipRect.height / 2;

                if (top < 0) {
                    caretTop += top;
                    top = 0;
                } else if (top > windowHeight - tooltipRect.height) {
                    caretTop += top - windowHeight + tooltipRect.height;
                    top = windowHeight - tooltipRect.height;
                }

                if (left > windowWidth - elementRect.width) {
                    left = Math.min(
                        elementRect.left + (windowWidth - elementRect.left) / 2,
                        left - elementRect.width / 2
                    );
                }

                return {
                    element: {
                        left: left + "px",
                        top: top + "px",
                        right,
                        bottom,
                    },
                    caret: { left: caretLeft + "px", top: caretTop + "px" },
                };
            } else if (this.position == "top") {
                let left =
                    elementRect.left +
                    (elementRect.width - tooltipRect.width) / 2;
                let top = elementRect.top + elementRect.height + 12;
                let right = "unset";
                let bottom = "unset";
                let caretLeft = tooltipRect.width / 2;
                let caretTop = -12;

                if (left < 0) {
                    caretLeft += left;
                    left = 0;
                } else if (left > windowWidth - tooltipRect.width) {
                    caretLeft += left - windowWidth + tooltipRect.width;
                    left = windowWidth - tooltipRect.width;
                }

                if (top > windowHeight - elementRect.height) {
                    top = Math.min(
                        elementRect.top + (windowHeight - elementRect.top) / 2,
                        top - elementRect.height / 2
                    );
                }

                return {
                    element: {
                        left: left + "px",
                        top: top + "px",
                        right,
                        bottom,
                    },
                    caret: { left: caretLeft + "px", top: caretTop + "px" },
                };
            } else if (this.position == "right") {
                let left = "unset";
                let top =
                    elementRect.top +
                    (elementRect.height - tooltipRect.height) / 2;
                let right = windowWidth - elementRect.left + 12;
                let bottom = "unset";
                let caretRight = 0;
                let caretTop = tooltipRect.height / 2;

                if (top < 0) {
                    caretTop += top;
                    top = 0;
                } else if (top > windowHeight - tooltipRect.height) {
                    caretTop += top - windowHeight + tooltipRect.height;
                    top = windowHeight - tooltipRect.height;
                }

                if (right > windowWidth - elementRect.width) {
                    right = Math.min(
                        Math.abs((windowWidth - elementRect.left) / 2),
                        Math.abs(right - elementRect.width / 2)
                    );
                }

                return {
                    element: {
                        left,
                        top: top + "px",
                        right: right + "px",
                        bottom,
                    },
                    caret: { right: caretRight + "px", top: caretTop + "px" },
                };
            } else {
                let left =
                    elementRect.left +
                    (elementRect.width - tooltipRect.width) / 2;
                let top = "unset";
                let right = "unset";
                let bottom = windowHeight - elementRect.top + 12;
                let caretLeft = tooltipRect.width / 2;
                let caretBottom = 0;

                if (left < 0) {
                    caretLeft += left;
                    left = 0;
                } else if (left > windowWidth - tooltipRect.width) {
                    caretLeft += left - windowWidth + tooltipRect.width;
                    left = windowWidth - tooltipRect.width;
                }

                if (bottom > windowHeight - elementRect.height) {
                    bottom = Math.min(
                        (windowHeight - elementRect.top) / 2,
                        bottom - elementRect.height / 2
                    );
                }

                return {
                    element: {
                        left: left + "px",
                        top,
                        right,
                        bottom: bottom + "px",
                    },
                    caret: {
                        left: caretLeft + "px",
                        bottom: caretBottom + "px",
                    },
                };
            }
        },

        topOverlayStyle() {
            if (!this.elementToShow) {
                return {};
            }

            this.update;

            let rect = this.elementToShow.element.getBoundingClientRect();

            return {
                top: 0,
                height: rect.top + "px",
                left: 0,
                right: 0,
            };
        },

        bottomOverlayStyle() {
            if (!this.elementToShow) {
                return {};
            }

            this.update;

            let rect = this.elementToShow.element.getBoundingClientRect();

            return {
                top: rect.top + rect.height + "px",
                bottom: 0,
                left: 0,
                right: 0,
            };
        },

        leftOverlayStyle() {
            if (!this.elementToShow) {
                return {};
            }

            this.update;

            let rect = this.elementToShow.element.getBoundingClientRect();

            return {
                top: rect.top + "px",
                height: rect.height + "px",
                left: 0,
                width: rect.left + "px",
            };
        },

        rightOverlayStyle() {
            if (!this.elementToShow) {
                return {};
            }

            this.update;

            let rect = this.elementToShow.element.getBoundingClientRect();

            return {
                top: rect.top + "px",
                height: rect.height + "px",
                left: rect.left + rect.width + "px",
                right: 0,
            };
        },
    },
};
</script>
