<template>
    <label class="hc-campaign-item-value-row">
        <div
            class="hc-campaign-item-value-row-label"
            v-text="'Utilisateur'"
        ></div>
        <div class="hc-campaign-item-value-row-select">
            <select v-model="user">
                <option
                    v-for="user in users"
                    :key="user.id"
                    :value="user.id"
                    v-text="user.name"
                ></option>
            </select>
        </div>
    </label>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import { UPDATE_CAMPAIGN_ACTION } from "@/actions/project/campaign";

export default {
    props: {
        action: {
            type: Object,
        },
    },

    computed: {
        ...mapGetters(["users"]),

        user: {
            get() {
                return this.action.value ? this.action.value.user : null;
            },
            set(value) {
                store.dispatch(UPDATE_CAMPAIGN_ACTION, {
                    id: this.action.id,
                    value: {
                        ...this.action.value,
                        user: value,
                    },
                });
            },
        },
    },
};
</script>
