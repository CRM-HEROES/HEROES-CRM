<template>
    <!--div class="hc-campaign-relation-arrow" :style="style"></!--div-->
    <svg :style="style">
        <path
            :d="path"
            stroke="#AAAAAA"
            fill="transparent"
            stroke-width="2"
        ></path>
        <path :d="arrowPath" fill="#AAAAAA"></path>
    </svg>
</template>

<style>
/*.hc-campaign-relation-arrow {
    position: absolute;
    top: -2px;
    left: 0;
    height: 5px;
    transform-origin: center left;
    cursor: pointer;
}

.relating .hc-campaign-relation-arrow {
    pointer-events: none;
}

.hc-campaign-relation-arrow:before {
    content: "×";
    display: inline-block;
    width: 100%;
    height: 0;
    border-top: 1px solid #000;
    position: absolute;
    top: 1px;
    left: 0;
    text-align: center;
    line-height: 0px;
    font-size: 0px;
    transition: font-size ease-out 100ms;
}

.hc-campaign-relation-arrow:after {
    content: "";
    display: inline-block;
    position: absolute;
    top: -3px;
    right: 0;
    width: 0;
    height: 1px;
    border-top: 4px solid transparent;
    border-bottom: 4px solid transparent;
    border-left: 7px solid #000;
    box-sizing: content-box;
    border-radius: 1px;
}

.hc-campaign-relation-arrow:hover:before {
    border-top-color: #f00;
    color: #f00;
    font-size: 19px;
}

.hc-campaign-relation-arrow:hover:after {
    border-left-color: #f00;
}*/
</style>

<script>
export default {
    props: {
        /**
         *
         */
        width: {
            type: Number,
            default: 0,
        },

        /**
         *
         */
        height: {
            type: Number,
            default: 0,
        },
    },

    computed: {
        style() {
            return {
                left: this.width >= 0 ? 0 : "unset",
                right: this.width >= 0 ? "unset" : 0,
                top: this.height >= 0 ? 0 : "unset",
                bottom: this.height >= 0 ? "unset" : 0,
                width: Math.abs(this.width),
                height: Math.abs(this.height) + 10,
                position: "absolute",
            };
        },

        path() {
            const curve = 100;

            const x1 = this.width >= 0 ? 0 : this.width;
            const y1 = this.height >= 0 ? 0 : -this.height + 10;

            const x2 = x1 + curve;
            const y2 = y1;

            const x3 = this.width >= 0 ? this.width - curve : -curve;
            const y3 = this.height >= 0 ? this.height : 10;

            const x4 = this.width >= 0 ? this.width : 0;
            const y4 = y3;

            return `M${x1} ${y1} C ${x2} ${y2}, ${x3} ${y3}, ${x4} ${y4}`;
        },

        arrowPath() {
            const x = this.width;
            const y = this.height >= 0 ? this.height : 10;

            return `M${x} ${y} L${x - 10} ${y - 7} L${x - 10} ${y + 7} z`;
        },
    },
};
</script>
