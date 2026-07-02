<template>
    <div id="hc-flash" :class="messages.length == 0 ? 'empty' : ''">
        <div id="hc-flash-content">
            <div
                v-for="(message, i) in messages"
                :class="
                    'alert-flash alert-' +
                    message.type +
                    (message.show ? ' show' : '')
                "
                :key="'flash-' + message.key"
                role="alert"
                @click.stop="message.action(message), hide(message)"
                :style="{
                    color: message.color ? message.color : '#000000',
                    backgroundColor: message.backgroundColor
                        ? message.backgroundColor
                        : '#FFFFFF',
                }"
            >
                <div class="alert-flash-header">
                    <div class="alert-flash-icon">
                        <i
                            :class="message.icon ? message.icon : 'fa fa-info'"
                        ></i>
                    </div>

                    <span
                        class="alert-flash-title"
                        v-html="message.title"
                    ></span>
                </div>

                <div class="alert-flash-content">
                    <span class="alert-flash-body" v-html="message.body"></span>
                </div>

                <div class="alert-flash-close">
                    <span @click.stop="hide(message)">&times;</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
#hc-flash {
    position: fixed;
    right: 0px;
    top: 60px;
    z-index: 1050;
    width: 300px;
    max-width: 100%;
    padding: 10px;
}

#hc-flash.empty {
    pointer-events: none;
}

#hc-flash-content {
    width: 100%;
    height: auto;
}

#hc-flash-content > .alert-flash {
    position: relative;
    margin-bottom: 0;
    border-radius: 5px;
    padding: 0 10px;
    border: none;
    margin-left: 100%;
    max-height: 0;
    transition: all 300ms cubic-bezier(0.15, 1, 0.33, 1);
    transition: max-height 100ms cubic-bezier(0.15, 1, 0.33, 1) 500ms,
        margin-bottom 100ms cubic-bezier(0.15, 1, 0.33, 1) 500ms,
        padding 100ms cubic-bezier(0.15, 1, 0.33, 1) 500ms,
        margin-left 600ms cubic-bezier(0.15, 1, 0.33, 1);
    cursor: pointer;
    width: 100%;
    background-color: #fff;
    color: #000000;
    float: left;
    /*border-left: 3px solid transparent;*/
}

#hc-flash-content > .alert-flash.alert-success {
    border-color: #138832;
    color: #138832;
}

#hc-flash-content > .alert-flash.alert-warning {
    border-color: #e68f3c;
    color: #e68f3c;
}

#hc-flash-content > .alert-flash.alert-danger {
    border-color: #e6503c;
    color: #e6503c;
}

#hc-flash-content > .alert-flash.show {
    margin-left: 0%;
    margin-bottom: 10px;
    max-height: 150px;
    padding: 5px 10px;
    transition: max-height 100ms cubic-bezier(0.15, 1, 0.33, 1),
        margin-bottom 100ms cubic-bezier(0.15, 1, 0.33, 1),
        padding 100ms cubic-bezier(0.15, 1, 0.33, 1),
        margin-left 500ms cubic-bezier(0.15, 1, 0.33, 1) 100ms;
    box-shadow: 0 20px 50px -20px #0004;
}

#hc-flash-content > .alert-flash > .alert-flash-header > .alert-flash-icon {
    display: inline-block;
    width: 22px;
    height: 30px;
    line-height: 30px;
    text-align: center;
    left: 15px;
    top: 15px;
    font-size: 13px;
    margin-right: 10px;
}

#hc-flash-content > .alert-flash > .alert-flash-header > .alert-flash-title {
    font-size: 11px;
    color: inherit;
    display: inline-block;
    line-height: 30px;
    vertical-align: top;
}

#hc-flash-content > .alert-flash > .alert-flash-content {
    float: left;
    width: 100%;
}

#hc-flash-content > .alert-flash > .alert-flash-content > .alert-flash-body {
    font-size: 12px;
    color: inherit;
    opacity: 0.9;
    display: inline-block;
    width: 100%;
}

#hc-flash-content > .alert-flash > .alert-flash-close {
    position: absolute;
    width: 30px;
    height: 30px;
    line-height: 30px;
    text-align: center;
    right: 5px;
    top: 5px;
    font-size: 18px;
    color: inherit;
    border-radius: 7px;
}

#hc-flash-content > .alert-flash > .alert-flash-close:hover {
    background-color: #0001;
}
</style>

<script>
import EventBus from "@/utils/event-bus";

export default {
    data() {
        return {
            messages: [],
            count: 0,
        };
    },

    created() {
        EventBus.on("flash", (message) => {
            this.flash(message);
        });
    },

    methods: {
        flash(message) {
            // create message object
            let m = {
                ...message,
                key: this.count,
                type:
                    typeof message.type == "undefined"
                        ? "success"
                        : message.type,
                show: false,
                action:
                    message.action !== undefined && message.action !== null
                        ? message.action
                        : () => {},
                duration:
                    message.duration !== undefined && message.duration !== null
                        ? message.duration
                        : 8000,
            };

            // add message to the list of flash message
            // to show
            this.messages = [m, ...this.messages];

            // show message
            setTimeout(() => {
                this.messages = this.messages.map((ms) =>
                    ms.key == m.key ? { ...ms, show: true } : ms
                );
            }, 50);

            // hide message after duration (ms)
            setTimeout(() => {
                this.hide(m);
            }, m.duration);

            ++this.count;
        },

        hide(message) {
            this.messages = this.messages.map((ms) =>
                ms.key == message.key ? { ...ms, show: false } : ms
            );

            setTimeout(() => {
                this.messages = this.messages.filter(
                    (m) => m.key != message.key
                );
            }, 1000);
        },

        icon(message) {
            if (message.type == "warning") {
                return "fa-exclamation-triangle";
            } else if (message.type == "danger") {
                return "fa-ban";
            } else if (message.type == "message") {
                return "fa-comment";
            } else {
                return "fa-info-circle";
            }
        },
    },
};
</script>
