<template>
    <tab-layout :count="2" :tab="aircallTab" class="hc-flex-1">
        <template #1>
            <div class="hc-flex-column" style="height: 100%">
                <item @click="$emit('back')" class="bordered">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('prospect.interaction.call_by_aircall')"
                    ></div>
                    <icon
                        class="fa fa-cog"
                        @click.stop="$emit('setting')"
                    />
                </item>
                <div
                    style="
                        flex: 1;
                        width: 100%;
                        height: 100%;
                        padding: 10px;
                        overflow: auto;
                    "
                >
                    <aircall
                        id="aircall-phone"
                        :number="interaction.number"
                        :style="{
                            width: '100%',
                            height: '100%',
                            display:
                                tab == 1 && frameTab == 0 ? 'block' : 'none',
                        }"
                        @outgoing-call="
                            (callInfos) => {
                                interaction.status = 'initiated';
                                interaction.data = {
                                    id: callInfos.call_id,
                                };
                                $emit('update-interaction');
                            }
                        "
                        @call-ended="
                            (callInfos) => {
                                interaction.status = 'ended';
                                interaction.data = {
                                    id: callInfos.call_id,
                                };
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
        <template #2>
            <div class="hc-flex-column" style="height: 100%">
                <item @click="$emit('back-setting')" class="bordered">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="'Paramètre Aircall Webhook'"
                    ></div>
                </item>
                <item-list class="hc-flex-1" gap="5px" style="overflow: auto">
                    <item
                        tag="a"
                        href="https://dashboard.aircall.io/integrations/flow/install/webhook/webhook/0"
                        target="_blank"
                    >
                        <icon class="fa fa-wifi" />
                        <div
                            class="hc-item-main-content"
                            v-text="
                                'Rendez-vous sur la page webhook d\'aircall'
                            "
                        ></div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item
                        tag="a"
                        @click.prevent="$emit('copy-webhook')"
                    >
                        <icon class="fa fa-link" />
                        <div
                            class="hc-item-main-content"
                            v-text="
                                'Mettre ' + aircallWebhookUrl + ' comme URL'
                            "
                        ></div>
                        <icon class="fa fa-copy" />
                    </item>
                    <item>
                        <icon class="fa fa-check" />
                        <div
                            class="hc-item-main-content"
                            v-text="
                                'Cocher &quot;call.ended&quot; dans la section Appel'
                            "
                        ></div>
                    </item>
                    <item>
                        <icon class="fa fa-check" />
                        <div
                            class="hc-item-main-content"
                            v-text="
                                'Enfin cliquer sur &quot;Ajouter webhook&quot;'
                            "
                        ></div>
                    </item>
                </item-list>
            </div>
        </template>
    </tab-layout>
</template>

<script>
import Aircall from "@/components/utils/Aircall.vue";

export default {
    components: {
        Aircall,
    },

    props: {
        interaction: {
            type: Object,
            required: true,
        },

        tab: {
            type: Number,
            required: true,
        },

        frameTab: {
            type: Number,
            required: true,
        },

        aircallTab: {
            type: Number,
            required: true,
        },

        aircallWebhookUrl: {
            type: String,
            required: true,
        },
    },

    emits: [
        "back",
        "setting",
        "back-setting",
        "copy-webhook",
        "update-interaction",
        "next-interaction",
    ],
};
</script>
