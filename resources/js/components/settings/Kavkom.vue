<template>
    <modal :name="name" title="Kavkom" @open="showSetting">
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

                <v-field label="Secret API" v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="setting.api_secret"
                /></v-field>
            </item-list>
            <buttons>
                <button
                    v-if="setting && setting.id"
                    @click.prevent="remove"
                    class="hc-button-danger"
                    v-text="$t('delete')"
                ></button>
                <button v-text="$t('update')"></button>
            </buttons>
            <loading
                :loading="updatingSetting || showingSetting || removingSetting"
                :title="loadingTitle"
            />
        </form>
    </modal>
</template>

<script>
import store from "@/store";
import ApiService from "@/apis/api.service";
import { mapGetters } from "vuex";

// Actions
import { CLOSE_MODAL } from "@/actions/modal";
import { UPDATE_SETTING, GET_SETTING } from "@/actions/project/setting";

export default {
    data() {
        return {
            name: "setting-kavkom",
            updatingSetting: false,
            showingSetting: false,
            removingSetting: false,
            loadingTitle: null,
            key: "kavkom",
            setting: {
                api_key: "",
                api_secret: "",
            },
        };
    },

    methods: {
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

    computed: {
        ...mapGetters(["project"]),
    },
};
</script>
