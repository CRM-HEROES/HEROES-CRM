<template>
    <div
        :style="{
            left: left + 'px',
            top: top + 'px',
            width: width + 'px',
            height: height + 'px',
            position: 'absolute',
            outline: '1px solid #0b82e4',
            border: '1px solid #0b82e4',
            backgroundColor: '#0b82e411',
            display: display ? 'block' : 'none',
            pointerEvents: 'none',
            transition: 'all ease-out 100ms',
        }"
    ></div>
</template>

<script>
export default {
    props: {
        display: Boolean,
        left: Number,
        top: Number,
        width: Number,
        height: Number,
    },
};
</script>
