<template>
    <modal :name="name" title="Email" @open="showSetting">
        <form
            class="hc-flex-column"
            style="height: 100%"
            @submit.prevent="update"
        >
            <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                <v-field label="Hôte" required v-slot="{ label }"
                    ><input
                        :placeholder="'smtp.example.com'"
                        v-model="setting.host"
                        required
                /></v-field>
                <v-field label="Port" required v-slot="{ label }"
                    ><input
                        :placeholder="'587, 443, ...'"
                        v-model="setting.port"
                        required
                /></v-field>
                <v-field label="Driver" required v-slot="{ label }"
                    ><input
                        :placeholder="'SMTP, ...'"
                        v-model="setting.driver"
                        required
                /></v-field>
                <v-field label="Username" required v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="setting.username"
                        required
                /></v-field>
                <v-field label="Mot de passe" required v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="setting.password"
                        type="password"
                        required
                /></v-field>
                <v-field label="Encryption" required v-slot="{ label }"
                    ><input
                        :placeholder="'ssl, tsl, ...'"
                        v-model="setting.encryption"
                        required
                /></v-field>
                <v-field label="Nom expéditeur" required v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="setting.from.name"
                        required
                /></v-field>
                <v-field label="Email expéditeur" required v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="setting.from.address"
                        type="email"
                        required
                /></v-field>
            </item-list>
            <buttons>
                <button v-text="$t('update')"></button>
                <button
                    class="hc-button-danger"
                    v-text="$t('delete')"
                    @click.prevent="remove"
                ></button>
            </buttons>
            <loading :loading="updatingSetting || showingSetting" />
        </form>
    </modal>
</template>

<script>
import store from "@/store";

// Actions
import { CLOSE_MODAL } from "@/actions/modal";
import {
    UPDATE_SETTING,
    REMOVE_SETTING,
    GET_SETTING,
} from "@/actions/project/setting";

export default {
    data() {
        return {
            name: "setting-email",
            updatingSetting: false,
            showingSetting: false,
            key: "email",
            setting: this.newSetting(),
        };
    },

    methods: {
        newSetting() {
            return {
                from: { name: "", address: "" },
                host: "",
                port: "",
                driver: "SMTP",
                password: "",
                username: "",
                encryption: "ssl",
            };
        },

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
        async remove() {
            this.updatingSetting = true;

            try {
                await store.dispatch(REMOVE_SETTING, this.key);
            } finally {
                this.updatingSetting = false;
                this.setting = this.newSetting();
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
