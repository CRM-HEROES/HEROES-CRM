<template>
    <!-- 
        Make sure that 
        the parent container 
        has a relative, fixed or absolute position 
    -->

    <div
        :class="'hc-loading-overlay' + (loading ? ' active' : '')"
        :label="title"
    >
        <div class="hc-loading active">
            <div class="hc-loading-spinner-layer">
                <div class="hc-loading-circle-clipper left">
                    <div class="hc-loading-circle"></div>
                </div>
                <div class="hc-loading-circle-clipper right">
                    <div class="hc-loading-circle"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
/** Animation keyframes */

@-webkit-keyframes hc-loading-container-rotate {
    to {
        -webkit-transform: rotate(360deg);
    }
}

@keyframes hc-loading-container-rotate {
    to {
        -webkit-transform: rotate(360deg);
        transform: rotate(360deg);
    }
}

@-webkit-keyframes hc-loading-fill-unfill-rotate {
    12.5% {
        -webkit-transform: rotate(135deg);
    }
    25% {
        -webkit-transform: rotate(270deg);
    }
    37.5% {
        -webkit-transform: rotate(405deg);
    }
    50% {
        -webkit-transform: rotate(540deg);
    }
    62.5% {
        -webkit-transform: rotate(675deg);
    }
    75% {
        -webkit-transform: rotate(810deg);
    }
    87.5% {
        -webkit-transform: rotate(945deg);
    }
    to {
        -webkit-transform: rotate(1080deg);
    }
}

@keyframes hc-loading-fill-unfill-rotate {
    12.5% {
        -webkit-transform: rotate(135deg);
        transform: rotate(135deg);
    }
    25% {
        -webkit-transform: rotate(270deg);
        transform: rotate(270deg);
    }
    37.5% {
        -webkit-transform: rotate(405deg);
        transform: rotate(405deg);
    }
    50% {
        -webkit-transform: rotate(540deg);
        transform: rotate(540deg);
    }
    62.5% {
        -webkit-transform: rotate(675deg);
        transform: rotate(675deg);
    }
    75% {
        -webkit-transform: rotate(810deg);
        transform: rotate(810deg);
    }
    87.5% {
        -webkit-transform: rotate(945deg);
        transform: rotate(945deg);
    }

    to {
        -webkit-transform: rotate(1080deg);
        transform: rotate(1080deg);
    }
}

@-webkit-keyframes hc-loading-left-spin {
    from {
        -webkit-transform: rotate(130deg);
    }
    50% {
        -webkit-transform: rotate(-5deg);
    }
    to {
        -webkit-transform: rotate(130deg);
    }
}

@keyframes hc-loading-left-spin {
    from {
        -webkit-transform: rotate(130deg);
        transform: rotate(130deg);
    }
    50% {
        -webkit-transform: rotate(-5deg);
        transform: rotate(-5deg);
    }
    to {
        -webkit-transform: rotate(130deg);
        transform: rotate(130deg);
    }
}

@-webkit-keyframes hc-loading-right-spin {
    from {
        -webkit-transform: rotate(-130deg);
    }
    50% {
        -webkit-transform: rotate(5deg);
    }
    to {
        -webkit-transform: rotate(-130deg);
    }
}

@keyframes hc-loading-right-spin {
    from {
        -webkit-transform: rotate(-130deg);
        transform: rotate(-130deg);
    }
    50% {
        -webkit-transform: rotate(5deg);
        transform: rotate(5deg);
    }
    to {
        -webkit-transform: rotate(-130deg);
        transform: rotate(-130deg);
    }
}

/** Loading overlay */

.hc-loading-overlay {
    background-color: rgba(255, 255, 255, 0.7);
    height: 100%;
    left: 0;
    opacity: 0;
    overflow: hidden;
    position: absolute;
    text-align: center;
    top: 0;
    transition: all 150ms ease-out;
    visibility: hidden;
    width: 100%;
    z-index: 10;
}

.hc-loading-overlay.active {
    opacity: 1;
    visibility: visible;
}

.hc-loading-overlay::before {
    content: "";
    display: inline-block;
    height: 100%;
    vertical-align: middle;
}

.hc-loading-overlay.active::after {
    content: attr(label);
    margin-left: 12px;
}

/** Loading */

.hc-loading {
    display: inline-block;
    height: 20px;
    position: relative;
    vertical-align: middle;
    width: 20px;
}

.active .hc-loading {
    -webkit-animation: hc-loading-container-rotate 1568ms linear infinite;
    animation: hc-loading-container-rotate 1568ms linear infinite;
}

/** Spinner layer */

.hc-loading-spinner-layer {
    height: 100%;
    opacity: 0;
    position: absolute;
    width: 100%;
}

.active .hc-loading-spinner-layer {
    -webkit-animation: hc-loading-fill-unfill-rotate 5332ms
        cubic-bezier(0.4, 0, 0.2, 1) infinite both;
    animation: hc-loading-fill-unfill-rotate 5332ms cubic-bezier(0.4, 0, 0.2, 1)
        infinite both;
    border-color: #1e88e5;
    opacity: 1;
}

/** Spinner circle clipper */

.hc-loading-circle-clipper {
    border-color: inherit;
    display: inline-block;
    height: 100%;
    overflow: hidden;
    position: relative;
    width: 50%;
}

/** Spinner circle */

.hc-loading-circle {
    border-radius: 50%;
}

.hc-loading-circle-clipper .hc-loading-circle {
    -webkit-animation: none;
    animation: none;
    border-bottom-color: transparent !important;
    border-color: inherit;
    border-radius: 50%;
    border-style: solid;
    border-width: 2px;
    bottom: 0;
    height: 100%;
    position: absolute;
    right: 0;
    top: 0;
    width: 200%;
}

/** Left spinner */

.hc-loading-circle-clipper.left {
    float: left !important;
}

.hc-loading-circle-clipper.left .hc-loading-circle {
    -webkit-transform: rotate(129deg);
    border-right-color: transparent !important;
    left: 0;
    transform: rotate(129deg);
}

.active .hc-loading-circle-clipper.left .hc-loading-circle {
    -webkit-animation: hc-loading-left-spin 1333ms cubic-bezier(0.4, 0, 0.2, 1)
        infinite both;
    animation: hc-loading-left-spin 1333ms cubic-bezier(0.4, 0, 0.2, 1) infinite
        both;
}

/** Right spinner */

.hc-loading-circle-clipper.right {
    float: right !important;
}

.hc-loading-circle-clipper.right .hc-loading-circle {
    -webkit-transform: rotate(-129deg);
    border-left-color: transparent !important;
    left: -100%;
    transform: rotate(-129deg);
}

.active .hc-loading-circle-clipper.right .hc-loading-circle {
    -webkit-animation: hc-loading-right-spin 1333ms cubic-bezier(0.4, 0, 0.2, 1)
        infinite both;
    animation: hc-loading-right-spin 1333ms cubic-bezier(0.4, 0, 0.2, 1)
        infinite both;
}

.dark .hc-loading-overlay {
    background-color: rgba(255, 255, 255, 0.2);
}
</style>

<script>
export default {
    props: {
        loading: {
            type: Boolean,
            default: false,
        },
        title: {
            type: String,
            default: null,
        },
    },
};
</script>
