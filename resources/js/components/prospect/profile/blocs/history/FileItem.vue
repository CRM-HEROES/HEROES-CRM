<template>
    <history-row
        :item="item"
        :date="item.created_at"
        :user="item.creator"
        icon="fa fa-folder"
    >
        <div class="hc-prospect-profile-history-file">
            <div class="hc-prospect-profile-history-file-info">
                <b
                    class="hc-prospect-profile-history-file-folder"
                    v-if="item.folder"
                    v-text="item.folder.name + ' : '"
                ></b>
                <div
                    class="hc-prospect-profile-history-file-name"
                    v-text="item.name"
                ></div>
            </div>
            <div class="hc-prospect-profile-history-file-thumbnail">
                <div class="hc-prospect-profile-history-file-thumbnail-content">
                    <img :src="item.thumbnail" />
                </div>
            </div>
        </div>
    </history-row>
</template>

<style>
.hc-prospect-profile-history-file {
    display: flex;
    flex-direction: row;
}

.hc-prospect-profile-history-file-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.hc-prospect-profile-history-file-thumbnail {
    width: 64px;
    border-radius: 5px;
    overflow: hidden;
    position: relative;
}

.hc-prospect-profile-history-file-thumbnail-content {
    text-align: center;
    width: 100%;
    padding-top: 80%;
}

.hc-prospect-profile-history-file-thumbnail-content > img {
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
// Components
import HistoryRow from "./Row.vue";

export default {
    props: {
        item: {
            type: Object,
        },
    },

    components: {
        HistoryRow,
    },
};
</script>
