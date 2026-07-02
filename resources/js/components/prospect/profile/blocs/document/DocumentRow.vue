<template>
    <a
        class="hc-prospect-profile-document hc-flex-column"
        :href="url"
        target="_blank"
        style="width: 50%"
    >
        <div class="hc-prospect-profile-document-thumbnail hc-flex-column">
            <div
                class="hc-prospect-profile-document-thumbnail-content"
                style="padding-top: 80%"
            >
                <img v-if="document.thumbnail" :src="document.thumbnail" />
            </div>
        </div>
        <div
            class="hc-prospect-profile-document-name"
            v-text="document.name"
        ></div>
    </a>
</template>

<style>
.hc-prospect-profile-document {
    padding: 3px;
    float: left;
    width: 33.333%;
    display: block;
    text-decoration: none;
    position: relative;
    border-radius: 10px;
    transition: all 200ms ease-out;
}

.hc-prospect-profile-document:hover {
    transform: scale(1.025);
    background-color: #ffffff;
}

.hc-prospect-profile-document-thumbnail {
    width: 100%;
    border-radius: 5px;
    overflow: hidden;
    position: relative;
    outline: 1px solid #cccccc;
}

.hc-prospect-profile-document-thumbnail-content {
    text-align: center;
    width: 100%;
    padding-top: 141.285%;
}

.hc-prospect-profile-document-thumbnail-content > img {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
}

.hc-prospect-profile-document-name {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    padding-top: 7px;
    color: #000000;
}
</style>

<script>
import { mapGetters } from "vuex";
import { API_URL } from "@/apis/common";

export default {
    props: {
        /**
         *
         */
        document: {
            type: Object,
        },
    },

    computed: {
        ...mapGetters(["project", "prospect"]),

        url() {
            return `${API_URL}/project/${this.project.slug}/prospect/${this.prospect.id}/document/${this.document.id}`;
        },
    },
};
</script>
