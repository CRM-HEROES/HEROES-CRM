<template>
    <input
        ref="input"
        :value="value"
        :style="{
            left: left + 'px',
            top: top + 'px',
            width: width + 'px',
            height: height + 'px',
            display: display ? 'block' : 'none',
            position: 'absolute',
            border: 'none',
            outline: '1px solid #0b82e4',
            border: '1px solid #0b82e4',
            boxShadow: '0 2px 5px #0008',
            backgroundColor: 'white',
            padding: '0 4px',
        }"
        @focus="$event.target.select()"
    />
</template>

<script>
export default {
    props: {
        display: Boolean,
        left: Number,
        top: Number,
        width: Number,
        height: Number,
        value: String,
    },

    watch: {
        value() {
            if (this.$refs.input) {
                // this.$refs.input.focus();
                // this.$refs.input.select();
            }
        },
    },
};
</script>
