<template>
    <item
        :class="[
            'hc-prospect-profile-interaction',
            'hc-flex-row',
            interaction.from_user
                ? ''
                : 'hc-prospect-profile-interaction-from-prospect',
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
                class="hc-prospect-profile-interaction-creator"
                v-text="
                    interaction.creator
                        ? interaction.creator.name
                        : '(Utilisateur inconnu)'
                "
            ></div>
            <div
                class="hc-prospect-profile-interaction-date"
                v-text="date"
            ></div>
        </div>

        <icon
            tag="a"
            v-if="interaction.audio"
            :href="interaction.audio"
            target="_blank"
            class="fa fa-microphone"
        />
    </item>
</template>

<style>
.hc-prospect-profile-interaction {
    padding: 5px 0;
}

.hc-prospect-profile-interaction-creator {
    color: #333333;
}

.hc-prospect-profile-interaction-date {
    font-size: 11px;
    color: #999999;
}
</style>

<script>
export default {
    props: {
        interaction: {
            type: Object,
        },
    },

    computed: {
        /**
         * Formatted date
         * that will be shown
         * in the interaction item
         * from the event.started_at field
         */
        date() {
            const date = dayjs(new Date(this.interaction.created_at)).format(
                "DD MMM YYYY HH:mm"
            );

            if (date == "Invalid date") {
                return this.interaction.created_at;
            }

            return date;
        },
    },
};
</script>
