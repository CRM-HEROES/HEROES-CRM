<template>
    <field-template
        type="map"
        :content="JSON.stringify(map.content)"
        @dragging="dragNewField"
        @dragged="dropNewField"
    >
        <div class="hc-document-map">
            <div class="hc-document-map-container">
                <div class="hc-document-map-content">
                    <img :src="thumbnail" />
                </div>
            </div>
        </div>
    </field-template>
</template>

<style>
.hc-document-map {
    width: 100%;
    float: left;
    padding: 7px;
    display: flex;
    flex-direction: column;
    cursor: pointer;
    border-radius: 5px;
}

.hc-document-map:hover {
    background-color: #eeeeee;
}

.hc-document-map-container {
    width: 100%;
    border-radius: 10px;
    overflow: hidden;
    position: relative;
}

.hc-document-map.selected .hc-document-map-container {
    outline: 2px solid #7939b8;
}

.hc-document-map-content {
    text-align: center;
    width: 100%;
    padding-top: 100%;
}

.hc-document-map-content > img {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
}
</style>

<script>
import FieldTemplate from "./FieldTemplate.vue";

export default {
    components: {
        FieldTemplate,
    },

    props: {
        map: {
            type: Object,
        },
    },

    data() {
        return {
            removingFile: false,
        };
    },

    methods: {
        dragNewField(field) {
            this.$emit("dragging", field);
        },

        dropNewField(x, y) {
            this.$emit("dragged", x, y);
        },
    },

    computed: {
        /**
         * Thumbnail URL
         */
        thumbnail() {
            return `${window.location.origin}/images/document/map/${this.map.image}.jpg`;
        },
    },
};
</script>
