<template>
    <div class="hc-prospect-uploading-attachment hc-flex-column">
        <div class="hc-prospect-uploading-attachment-thumbnail hc-flex-column">
            <div class="hc-prospect-uploading-attachment-thumbnail-content">
                <img :src="thumbnail" />
                <div
                    class="hc-prospect-uploading-attachment-remove"
                    @click.prevent.stop="remove"
                >
                    ×
                </div>
            </div>
        </div>
        <div
            class="hc-prospect-uploading-attachment-name"
            v-text="attachment.name"
        ></div>
    </div>
</template>

<style>
.hc-prospect-uploading-attachment {
    padding: 5px;
    float: left;
    min-width: 90px;
    display: block;
    text-decoration: none;
    position: relative;
    border-radius: 10px;
    transition: all 200ms ease-out;
}

.hc-prospect-uploading-attachment-thumbnail {
    width: 100%;
    border-radius: 5px;
    overflow: hidden;
    position: relative;
    background-color: #eee;
    box-shadow: 0 2px 5px -2px #0007;
}

.hc-prospect-uploading-attachment-thumbnail-content {
    text-align: center;
    width: 100%;
    padding-top: 70%;
}

.hc-prospect-uploading-attachment-thumbnail-content > img {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
}

.hc-prospect-uploading-attachment-remove {
    opacity: 0;
    transform: scale(0.8);
    position: absolute;
    top: 2px;
    right: 2px;
    width: 20px;
    height: 20px;
    transition: all 100ms ease-out;
    color: #ffffff;
    font-size: 9px;
    font-weight: bold;
    text-align: center;
    line-height: 20px;
    text-decoration: none;
    border-radius: 50%;
    cursor: pointer;
    background-color: #7939b8;
}

.hc-prospect-uploading-attachment:hover
    .hc-prospect-uploading-attachment-remove {
    opacity: 1;
    transform: scale(1);
}

.hc-prospect-uploading-attachment-name,
.hc-prospect-uploading-attachment-date {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.hc-prospect-uploading-attachment-name {
    padding-top: 3px;
    color: #000000;
}

.hc-prospect-uploading-attachment-date {
    font-size: 11px;
    color: #777;
}
</style>

<script>
export default {
    props: {
        /**
         *
         */
        attachment: {
            type: Object,
        },
    },

    data() {
        return {
            thumbnail: null,
            uploadProgress: 0,
        };
    },

    created() {
        this.loadThumbnail();
    },

    methods: {
        remove() {
            this.$emit("remove", this.attachment);
        },

        /**
         * Remove message
         */
        loadThumbnail() {
            if (!this.isImage) {
                return;
            }

            var reader = new FileReader();

            reader.onload = (e) => {
                this.thumbnail = e.target.result;
            };

            reader.readAsDataURL(this.attachment);
        },
    },

    computed: {
        isImage() {
            return this.attachment.type.split("/")[0] === "image";
        },
    },
};
</script>
