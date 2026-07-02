<template>
    <bloc
        icon="fa fa-comment icon-purple"
        :name="$t('prospect.profile.bloc.sms')"
    >
        <template #options>
            <icon
                tag="a"
                class="fa fa-paper-plane"
                @click.prevent.stop="manageSms"
                v-if="prospect.mobile_phone_number"
            />
        </template>
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <sms-row
                    v-for="(sms, i) in prospectSms"
                    :key="sms.id"
                    :sms="sms"
                />
            </div>
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";
import { SET_PROSPECT } from "@/actions/project/prospect";

// Components
import Bloc from "@/components/prospect/profile/blocs/Bloc.vue";
import SmsRow from "./SmsRow.vue";

export default {
    components: {
        Bloc,
        SmsRow,
    },

    methods: {
        /**
         * Manage prospect sms
         * See: @/components/prospect/sms/Slide.vue
         */
        manageSms() {
            store.commit(OPEN_SLIDE, "prospect-manage-sms");
            store.commit(SET_PROSPECT, this.prospect);
        },
    },

    computed: {
        ...mapGetters(["project", "prospect", "prospectSms"]),
    },
};
</script>
