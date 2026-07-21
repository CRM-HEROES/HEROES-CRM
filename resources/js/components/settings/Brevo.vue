<template>
    <modal :name="name" title="Brevo" @open="showSetting">
        <form
            class="hc-flex-column"
            style="height: 100%"
            @submit.prevent="update"
        >
            <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                <v-field label="Clé API" required v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="setting.api_key"
                        required
                /></v-field>
                <v-field label="Expéditeur (Sender)" required v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="setting.sender"
                        maxlength="15"
                        required
                /></v-field>
            </item-list>
            <buttons>
                <button v-text="$t('update')"></button>
            </buttons>
            <loading :loading="updatingSetting || showingSetting" />
        </form>
    </modal>
</template>

<script>
import store from "@/store";

// Actions
import { CLOSE_MODAL } from "@/actions/modal";
import { UPDATE_SETTING, GET_SETTING } from "@/actions/project/setting";

export default {
    data() {
        return {
            name: "setting-brevo",
            updatingSetting: false,
            showingSetting: false,
            key: "brevo",
            setting: {
                api_key: "",
                sender: "",
            },
        };
    },

    methods: {
        /**
         *
         */
        async update() {
            this.updatingSetting = true;

            try {
                await store.dispatch(UPDATE_SETTING, {
                    key: this.key,
                    value: this.setting,
                });
            } finally {
                this.updatingSetting = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        async showSetting() {
            this.showingSetting = true;

            try {
                const data = await store.dispatch(GET_SETTING, this.key);

                if (data) {
                    this.setting = data;
                }
            } finally {
                this.showingSetting = false;
            }
        },
    },
};
</script>