<template>
    <a class="hc-prospect-attachment hc-flex-column">
        <div class="hc-prospect-attachment-thumbnail hc-flex-column">
            <div class="hc-prospect-attachment-thumbnail-content">
                <img :src="attachment.thumbnail" />
            </div>
        </div>
        <div class="hc-prospect-attachment-name" v-text="attachment.name"></div>
    </a>
</template>

<style>
.hc-prospect-attachment {
    float: left;
    width: 30%;
    display: block;
    text-decoration: none;
    position: relative;
    border-radius: 10px;
    transition: all 200ms ease-out;
}

.hc-prospect-attachment:hover {
    transform: scale(1.05);
}

.hc-prospect-attachment-thumbnail {
    width: 100%;
    border-radius: 5px;
    overflow: hidden;
    position: relative;
    background-color: #eee;
    box-shadow: 0 2px 5px -2px #0007;
}

.hc-prospect-attachment-thumbnail-content {
    text-align: center;
    width: 100%;
    padding-top: 70%;
}

.hc-prospect-attachment-thumbnail-content > img {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
}

.hc-prospect-attachment-name,
.hc-prospect-attachment-date {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.hc-prospect-attachment-name {
    padding-top: 3px;
    color: #000000;
}

.hc-prospect-attachment-date {
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
};
</script>
