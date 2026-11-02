<template>
    <history-row
        :item="item"
        :date="item.created_at"
        :user="item.creator"
        icon="fa fa-phone"
    >
        <div class="hc-prospect-profile-history-label">
            <b
                class="hc-prospect-profile-history-label-category"
                v-text="'Appelé sur : '"
            ></b>
            <div
                class="hc-prospect-profile-history-label-name"
                v-text="source"
            ></div>
        </div>
        <button
            v-if="item.audio"
            type="button"
            class="hc-prospect-profile-history-audio"
            :title="audioTitle"
            :aria-label="audioTitle"
            @click.stop.prevent="toggleAudio"
        >
            <i :class="audioIcon" aria-hidden="true"></i>
        </button>
        <audio
            v-if="item.audio"
            ref="audio"
            :src="item.audio"
            preload="none"
            @play="playingAudio = true"
            @pause="playingAudio = false"
            @ended="playingAudio = false"
        ></audio>
    </history-row>
</template>

<style>
.hc-prospect-profile-history-audio {
    flex: 0 0 auto;
    border: 0;
    padding: 4px;
    color: #1e6ee5;
    background: transparent;
    cursor: pointer;
}

.hc-prospect-profile-history-audio:hover,
.hc-prospect-profile-history-audio:focus-visible {
    color: #1452ad;
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

    data() {
        return {
            playingAudio: false,
        };
    },

    computed: {
        audioIcon() {
            if (this.playingAudio) {
                return "fa fa-pause-circle";
            }

            return this.item.source == "cloudtalk"
                ? "fa fa-play-circle"
                : "fa fa-microphone";
        },

        audioTitle() {
            return this.item.source == "cloudtalk"
                ? "Ecouter la conversation"
                : "Ecouter l'enregistrement";
        },

        source() {
            if (this.item.source == "telephone") {
                return "Téléphone";
            }

            if (this.item.source == "ringover") {
                return "Ringover";
            }

            if (this.item.source == "aircall") {
                return "Aircall";
            }

            if (this.item.source == "cloudtalk") {
                return "CloudTalk";
            }

            return this.item.source;
        },
    },

    methods: {
        toggleAudio() {
            const audio = this.$refs.audio;

            if (!audio) {
                return;
            }

            if (this.playingAudio) {
                audio.pause();
                return;
            }

            const promise = audio.play();
            if (promise && promise.catch) {
                promise.catch(() => {
                    this.playingAudio = false;
                });
            }
        },
    },
};
</script>
