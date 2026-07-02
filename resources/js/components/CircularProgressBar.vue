<template>
    <div
        class="hc-circular-progress-bar"
        :aria-valuenow="progress"
        aria-valuemin="0"
        aria-valuemax="100"
        :style="
            '--value:' +
            progress +
            ';--size:' +
            size +
            'px;--fg:' +
            color +
            'px'
        "
    ></div>
</template>

<style>
@keyframes growCircularProgressBar {
    0%,
    33% {
        --circularProgressBarPercentage: 0;
    }

    to {
        --circularProgressBarPercentage: var(--value);
    }
}

@property --circularProgressBarPercentage {
    syntax: "<number>";
    inherits: false;
    initial-value: 0;
}

.hc-circular-progress-bar {
    --bg: #ffffff;
    --circularProgressBarPercentage: var(--value);
    -webkit-animation: growCircularProgressBar 1s 1 forwards;
    animation: growCircularProgressBar 1s 1 forwards;
    width: var(--size);
    height: var(--size);
    border-radius: 50%;
    display: inline-block;
    line-height: var(--size);
    background: radial-gradient(
            closest-side,
            #fff 80%,
            transparent 0 99.9%,
            #fff 0
        ),
        conic-gradient(
            var(--fg) calc(var(--circularProgressBarPercentage) * 1%),
            var(--bg) 0
        );
    font-size: calc(var(--size) / 4);
    color: var(--fg);
}
</style>

<script>
export default {
    props: {
        progress: {
            type: Number,
            default: 0,
        },
        size: {
            type: Number,
            default: 30,
        },
        color: {
            type: String,
            default: "#1e88e5",
        },
    },
};
</script>
