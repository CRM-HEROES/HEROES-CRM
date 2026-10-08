<template>
    <item
        :class="[
            'hc-prospect-interaction',
            'hc-flex-row',
            interaction.from_user
                ? ''
                : 'hc-prospect-interaction-from-prospect',
        ]"
    >
        <icon v-if="interaction.source == 'aircall'">
            <svg viewBox="0 0 40 40">
                <path
                    fill="#00B388"
                    d="M39.1,9.8c-0.9-4.5-4.5-8-9-9C27.9,0.3,24.2,0,20,0S12.1,0.3,9.9,0.8c-4.5,0.9-8,4.5-9,9 c-0.5,2.3-0.9,6-0.9,10.2c0,4.2,0.3,7.9,0.9,10.2c0.9,4.5,4.5,8,9,9C12.1,39.6,15.8,40,20,40s7.9-0.3,10.1-0.9c4.5-0.9,8-4.5,9-9 c0.5-2.3,0.9-6,0.9-10.2C39.9,15.8,39.6,12.1,39.1,9.8z M29.3,30.5C29.3,30.5,29.3,30.5,29.3,30.5c-0.7,0.3-1.9,0.5-3.5,0.6 c-0.1,0-0.1,0-0.2,0c-0.3,0-0.6-0.2-0.8-0.5c-0.4-0.9-1.2-1.6-2.2-1.8c-0.6-0.1-1.5-0.2-2.6-0.2s-2,0.1-2.6,0.2 c-1,0.2-1.8,0.9-2.2,1.8c-0.1,0.3-0.4,0.5-0.8,0.5c-0.1,0-0.2,0-0.2,0c-1.6-0.2-2.8-0.4-3.5-0.6c0,0,0,0,0,0 c-0.5-0.2-0.8-0.6-0.8-1.2c0,0,0,0,0,0c0,0,0,0,0-0.1c0,0,0,0,0,0c0,0,0,0,0,0c0.1-1.6,1.1-5.5,2.6-9.8c1.7-5,3.5-9,4.2-9.8 c0.1-0.1,0.3-0.2,0.4-0.3c0.1,0,0.1-0.1,0.2-0.1c0,0,0,0,0,0c0.5-0.2,1.5-0.3,2.6-0.3c1.1,0,2.1,0.1,2.6,0.3c0,0,0,0,0,0 c0.1,0,0.2,0.1,0.2,0.1c0.2,0.1,0.3,0.2,0.4,0.3c0,0,0,0,0,0c0.8,0.8,2.6,4.8,4.2,9.8c1.5,4.4,2.5,8.2,2.6,9.8c0,0,0,0,0,0 c0,0,0,0,0,0c0,0,0,0,0,0.1c0,0,0,0,0,0C30.1,29.8,29.8,30.3,29.3,30.5z"
                ></path>
            </svg>
        </icon>
        <icon v-else-if="interaction.source == 'ringover'">
            <svg viewBox="0 0 40 40">
                <path
                    d="M9.9,16.9c1.3-4.3,5.3-7.4,10.1-7.4s8.7,3.1,10.1,7.4h9.7C38.2,7.3,30,0,20,0S1.8,7.3,0.3,16.9H9.9z"
                    style="fill: rgb(85, 195, 192)"
                ></path>
                <path
                    d="M30.1,23.1c-1.3,4.3-5.3,7.4-10.1,7.4s-8.7-3.1-10.1-7.4H0.3C1.8,32.7,10,40,20,40s18.2-7.3,19.7-16.9H30.1z"
                    style="fill: rgb(85, 195, 192)"
                ></path>
            </svg>
        </icon>
        <icon v-else class="fa fa-phone" />

        <div class="hc-item-main-content hc-flex-column">
            <div
                class="hc-prospect-interaction-creator"
                v-text="
                    interaction.creator
                        ? interaction.creator.name
                        : '(Utilisateur inconnu)'
                "
            ></div>
            <div class="hc-prospect-interaction-date">
                <span v-text="date"></span>
                <span
                    v-if="durationLabel"
                    class="hc-prospect-interaction-duration"
                    v-text="' - ' + durationLabel"
                ></span>
            </div>
        </div>

        <div class="hc-flex-column" style="align-items: flex-end">
            <div
                v-if="interaction.number"
                class="hc-prospect-interaction-number"
                v-text="interaction.number"
            ></div>
            <div
                v-if="interaction.from_number"
                class="hc-prospect-interaction-from-number"
                v-text="interaction.from_number"
            ></div>
        </div>

        <icon
            tag="button"
            v-if="interaction.audio"
            type="button"
            @click.prevent.stop="toggleAudio"
            :class="audioIcon"
            :title="audioTitle"
        />
        <audio
            v-if="interaction.audio"
            ref="audio"
            :src="interaction.audio"
            preload="none"
            @play="playingAudio = true"
            @pause="playingAudio = false"
            @ended="playingAudio = false"
        ></audio>
    </item>
</template>

<style>
.hc-prospect-interaction-creator {
    color: #333333;
}
.hc-prospect-interaction-date {
    font-size: 11px;
    color: #999999;
}
.hc-prospect-interaction-duration {
    color: #666666;
}
.hc-prospect-interaction-number {
    font-size: 11px;
    color: #999999;
}
.hc-prospect-interaction-from-number {
    font-size: 11px;
    color: #7939b8;
}
</style>

<script>
export default {
    props: {
        interaction: {
            type: Object,
        },
    },

    data() {
        return {
            playingAudio: false,
        };
    },

    computed: {
        /**
         *
         */
        date() {
            return dayjs(this.interaction.created_at).fromNow();
        },

        durationLabel() {
            const duration = this.interaction.duration;

            if (duration === null || duration === undefined || duration === "") {
                return "";
            }

            const seconds = parseInt(duration, 10);

            if (Number.isNaN(seconds) || seconds < 0) {
                return "";
            }

            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;

            return `${minutes}:${String(remainingSeconds).padStart(2, "0")}`;
        },

        audioIcon() {
            if (this.playingAudio) {
                return "fa fa-pause-circle";
            }

            return this.interaction.source == "cloudtalk"
                ? "fa fa-play-circle"
                : "fa fa-microphone";
        },

        audioTitle() {
            return this.interaction.source == "cloudtalk"
                ? "Ecouter la conversation"
                : "Ecouter l'enregistrement";
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
