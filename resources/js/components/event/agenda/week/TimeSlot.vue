<template>
    <div
        :class="[
            'hc-agenda-time-slot',
            minute == 0
                ? 'hc-agenda-time-slot-hour'
                : minute == 30
                ? ''
                : 'hc-agenda-time-slot-14-45',
        ]"
        v-text="label"
    ></div>
</template>

<style>
.hc-agenda-time-slot {
    font-size: 10px;
    width: 100%;
    height: 20px;
    padding: 0 10px;
    line-height: 20px;
    text-align: right;
    color: #aaaaaa;
    position: sticky;
    left: 0;
    background-color: white;
    z-index: 1;
}

.hc-agenda-time-slot:before {
    border: 1px solid #f7f7f7;
    bottom: 0px;
    content: "";
    display: inline-block;
    left: -1px;
    pointer-events: none;
    position: absolute;
    right: 0px;
    top: -1px;
}

.hc-agenda-time-slot.hc-agenda-time-slot-hour {
    font-size: 12px;
    color: #000000;
}

.hc-agenda-time-slot-14-45 {
    color: transparent;
}

@media (max-width: 767px) {
    .hc-agenda-time-slot {
        height: 30px;
    }
}
</style>

<script>
export default {
    props: {
        hour: {
            type: Number,
        },

        minute: {
            type: Number,
        },
    },

    computed: {
        label() {
            return (
                (this.hour < 10 ? "0" : "") +
                this.hour +
                ":" +
                (this.minute < 10 ? "0" : "") +
                this.minute
            );
        },
    },
};
</script>
