<template>
    <div class="hc-flex-column" style="height: 100%">
        <item @click="$emit('back')" class="bordered">
            <icon class="fa fa-caret-left" />
            <div
                class="hc-item-main-content"
                v-text="$t('prospect.interaction.call_by_ringover')"
            ></div>
            <icon class="fa fa-cog" @click.stop="$emit('setting')" />
        </item>
        <div
            style="
                flex: 1;
                width: 100%;
                height: 100%;
                overflow: auto;
            "
        >
            <ringover
                id="ringover-phone"
                :number="interaction.number"
                tab="phone"
                style="flex: 1; width: 100%; height: 100%"
                @ringing-call="
                    (callInfo) => {
                        interaction.from_number = callInfo.data.from;
                        interaction.status = 'ringing';
                        interaction.data.id = callInfo.data.call_id;
                        $emit('update-interaction');
                    }
                "
                @hangup-call="
                    (callInfo) => {
                        interaction.status = 'hangup';
                        interaction.data.id = callInfo.data.call_id;
                        $emit('update-interaction');
                        $emit('next-interaction');
                    }
                "
                @answered-call="
                    (interaction.status = 'answered'),
                        $emit('update-interaction')
                "
            />
        </div>
    </div>
</template>

<script>
import Ringover from "@/components/utils/Ringover.vue";

export default {
    components: {
        Ringover,
    },

    props: {
        interaction: {
            type: Object,
            required: true,
        },
    },

    emits: [
        "back",
        "setting",
        "update-interaction",
        "next-interaction",
    ],
};
</script>
