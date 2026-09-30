<template>
    <bloc icon="fa fa-cog" :name="'API'">
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <!-- Mail -->
                <item @click="configureEmail">
                    <icon class="fa fa-envelope" />
                    <div class="hc-item-main-content">Mail</div>
                </item>

                <!-- SMSBOX -->
                <item @click="configureSmsbox">
                    <icon>
                        <svg viewBox="0 0 50 40">
                            <path
                                style="fill: #7d3a96"
                                d="M42.8,40H7.2c-4,0-7.2-3.2-7.2-7.2V7c0-4,3.2-7.2,7.2-7.2h35.7C46.8-0.2,50,3,50,7v25.8 C50,36.8,46.8,40,42.8,40z"
                            />
                            <path
                                style="fill: #ffffff"
                                d="M26.6,34.3h-3.2c-4,0-7.3-3.3-7.3-7.3V13.6c0-4,3.3-7.3,7.3-7.3h3.2c4,0,7.3,3.3,7.3,7.3V27 C33.8,31,30.6,34.3,26.6,34.3z"
                            />
                            <path
                                style="fill: #7e3b96"
                                d="M27.4,23.4h-4.7c-2.2,0-3.9-1.8-3.9-3.9v-6.4c0-2.2,1.8-3.9,3.9-3.9h4.7c2.2,0,3.9,1.8,3.9,3.9v6.4 C31.3,21.7,29.5,23.4,27.4,23.4z"
                            />
                            <path
                                style="fill: #ffffff"
                                d="M31.9,11.8L31.9,11.8c-0.8,0-1.4-0.6-1.4-1.4V5.3c0-0.8,0.6-1.4,1.4-1.4h0c0.8,0,1.4,0.6,1.4,1.4v5.2 C33.3,11.2,32.7,11.8,31.9,11.8z"
                            />
                        </svg>
                    </icon>
                    <div class="hc-item-main-content">SMS via SMSBOX</div>
                </item>

                <!-- UltraMsg -->
                <item @click="configureUltramsg">
                    <icon>
                        <svg viewBox="0 0 50 50">
                            <path
                                style="fill: #07b49b"
                                d="M0.8,28.2l2.6-2.6C3.8,25.2,4.4,25,4.9,25h10v19.8l-2.6,2.6c-0.4,0.4-0.9,0.6-1.5,0.6h-10V28.2z"
                            />
                            <path
                                style="fill: #07b49b"
                                d="M18,17.4l2.6-2.6c0.4-0.4,0.9-0.6,1.5-0.6h10v30.7l-2.6,2.6C29,47.8,28.5,48,27.9,48H18V17.4z"
                            />
                            <path
                                style="fill: #07b49b"
                                d="M35.1,6l2.6-2.6c0.4-0.4,0.9-0.6,1.5-0.6h10v42l-2.6,2.6c-0.4,0.4-0.9,0.6-1.5,0.6h-10V6z"
                            />
                        </svg>
                    </icon>
                    <div class="hc-item-main-content">UltraMsg</div>
                </item>

                <!-- MTarget -->
                <item @click="configureMTarget">
                    <icon>
                        <svg viewBox="0 0 50 50">
                            <path
                                style="fill: #11b37d"
                                d="M9.4,47c0-11.9,0-23.9,0.1-35.8c2.9,11.9,5.8,23.9,8.7,35.8h14.3c2.9-11.9,5.8-23.8,8.7-35.7 c0.1,11.9,0.1,23.8,0.1,35.7H50c0-14.8,0-29.7,0-44.5c-5,0-10.1,0-15.1,0.1c-3.2,13-6.3,26-9.5,39c-3.3-13-6.5-26-9.6-39.1 c-5,0-10,0-15.1,0c0,14.8,0,29.7,0,44.5H9.4z"
                            />
                        </svg>
                    </icon>
                    <div class="hc-item-main-content">SMS via MTarget</div>
                </item>
            </div>
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";

// Components
import Bloc from "@/components/project/profile/blocs/Bloc.vue";

export default {
    components: {
        Bloc,
    },

    methods: {
        /**
         *
         */
        configureEmail() {
            store.commit(OPEN_MODAL, "setting-email");
        },

        configureSmsbox() {
            store.commit(OPEN_MODAL, "setting-smsbox");
        },

        configureUltramsg() {
            store.commit(OPEN_MODAL, "setting-ultramsg");
        },

        configureMTarget() {
            store.commit(OPEN_MODAL, "setting-mtarget");
        },
    },

    computed: {
        ...mapGetters(["project", "roles"]),
    },
};
</script>
