<template>
    <label class="hc-campaign-item-value-row">
        <div
            class="hc-campaign-item-value-row-label"
            v-text="'Type de RDV'"
        ></div>
        <div class="hc-campaign-item-value-row-select">
            <select v-model="calendar">
                <option
                    v-for="calendar in calendars"
                    :key="calendar.id"
                    :value="calendar.id"
                    v-text="calendar.name"
                ></option>
            </select>
        </div>
    </label>
    <label class="hc-campaign-item-value-row">
        <div
            class="hc-campaign-item-value-row-label"
            v-text="$t('campaign.action.prospect.event.user.title')"
        ></div>
        <div class="hc-campaign-item-value-row-select">
            <select v-model="user">
                <option
                    v-for="user in allUsers"
                    :key="user.id"
                    :value="user.id"
                    v-text="user.name"
                ></option>
            </select>
        </div>
    </label>
    <label class="hc-campaign-item-value-row">
        <div
            class="hc-campaign-item-value-row-label"
            v-text="'Envoyer dans X jours au minimum'"
        ></div>
        <div class="hc-campaign-item-value-row-select">
            <select v-model="min_days">
                <option v-for="i in 30" :value="i - 1" v-text="i - 1"></option>
            </select>
        </div>
    </label>
    <label class="hc-campaign-item-value-row">
        <div
            class="hc-campaign-item-value-row-label"
            v-text="'Envoyer dans X jours au maximum'"
        ></div>
        <div class="hc-campaign-item-value-row-select">
            <select v-model="max_days">
                <option v-for="i in 30" :value="i - 1" v-text="i - 1"></option>
            </select>
        </div>
    </label>
    <label class="hc-campaign-item-value-row">
        <div class="hc-campaign-item-value-row-label" v-text="'Durée'"></div>
        <div class="hc-campaign-item-value-row-select">
            <select v-model="duration">
                <option
                    v-for="i in 8"
                    :value="i * 15"
                    v-text="i * 15 + 'mn'"
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
        ...mapGetters(["project", "calendars", "users", "roles"]),

        allUsers() {
            return [
                {
                    id: "random",
                    name: this.$t("campaign.action.prospect.event.user.random"),
                },
                {
                    id: "prospect-creator",
                    name: this.$t(
                        "campaign.action.prospect.event.user.prospect_creator"
                    ),
                },
                {
                    id: "prospect-affected-user",
                    name: this.$t(
                        "campaign.action.prospect.event.user.prospect_affected_user"
                    ),
                },
                ...this.users,
                ...this.roles.map((role) => ({
                    id: "role." + role.id,
                    name: this.$t("campaign.action.prospect.event.user.role", {
                        role: role.name,
                    }),
                })),
            ];
        },

        ...Object.fromEntries(
            ["calendar", "user", "min_days", "max_days", "duration"].map(
                (key) => [
                    key,
                    {
                        // Get selected field CSS value
                        get() {
                            return this.action.value[key]
                                ? this.action.value[key]
                                : "";
                        },
                        // Set selected field CSS value
                        set(value) {
                            store.dispatch(UPDATE_CAMPAIGN_ACTION, {
                                id: this.action.id,
                                value: {
                                    ...this.action.value,
                                    [key]: value,
                                },
                            });
                        },
                    },
                ]
            )
        ),
    },
};
</script>
