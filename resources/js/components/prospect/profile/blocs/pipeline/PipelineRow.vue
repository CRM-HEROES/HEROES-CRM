<template>
    <a
        :class="['hc-prospect-profile-pipeline', 'hc-flex-column', active ? 'active' : '']"
        :href="pipeline.url"
        :style="{width: width + '%'}"
        @click.prevent="updatePipeline"
    >
        <span
            v-text="label.name"
            v-tooltip.bottom="label.name"
        ></span>
    </a>
</template>

<style>
.hc-prospect-profile-pipeline {
    display: inline-block;
    position: relative;
    margin-left: -2px;
    cursor: pointer;
}

.hc-prospect-profile-pipeline > span {
    display: inline-block;
    width: 100%;
    height: 30px;
    line-height: 30px;
    background-color: #489f1f;
    padding: 0 12px;
    clip-path: polygon(calc(100% - 4px) 0, 100% 50%, calc(100% - 4px) 100%, 2px 100%, 6px 50%, 2px 0);
    text-overflow: ellipsis;
    overflow: hidden;
    float: left;
    color: white;
    font-size: 11px;
}

.no-selected > .hc-prospect-profile-pipeline > span {
    background-color: #E5E5E5;
    color: #333;
}

div:hover > .hc-prospect-profile-pipeline > span {
    background-color: #007a37;
    color: white;
}

.active ~ .hc-prospect-profile-pipeline > span,
.hc-prospect-profile-pipeline:hover ~ .hc-prospect-profile-pipeline > span {
    background-color: #E5E5E5 !important;
    color: #333 !important;
}

</style>

<script>
import store from "@/store";
import { UPDATE_PROSPECT_PIPELINE_LABEL } from "@/actions/project/prospect/pipeline";

export default {
    props: {
        /**
         *
         */
        pipeline: {
            type: Object,
        },
        
        /**
         *
         */
        label: {
            type: Object,
        },
        
        /**
         *
         */
        active: {
            type: Object,
        },
    },

    data() {
        return {
            updatingPipeline: false,
        };
    },

    methods: {
        async updatePipeline() {
            this.updatingPipeline = true;

            try {
                store.dispatch(UPDATE_PROSPECT_PIPELINE_LABEL, {
                    pipeline: this.pipeline,
                    label: this.label
                })
            } finally {
                this.updatingPipeline = false;
            }
        },
    },

    computed: {
        /**
         *
         */
        width() {
            return 100 / this.pipeline.labels.length;
        },
    },
};
</script>
