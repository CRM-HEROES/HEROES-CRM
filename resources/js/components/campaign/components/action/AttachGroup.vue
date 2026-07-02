<template>
    <label class="hc-campaign-item-value-row">
        <div class="hc-campaign-item-value-row-label" v-text="'Groupe'"></div>
        <div class="hc-campaign-item-value-row-select">
            <select v-model="group">
                <option
                    v-for="group in groups"
                    :key="group.id"
                    :value="group.id"
                    v-text="group.name"
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
        ...mapGetters(["groups"]),

        group: {
            get() {
                return this.action.value ? this.action.value.group : null;
            },
            set(value) {
                store.dispatch(UPDATE_CAMPAIGN_ACTION, {
                    id: this.action.id,
                    value: {
                        ...this.action.value,
                        group: value,
                    },
                });
            },
        },
    },
};
</script>
