<template>
    <label class="hc-campaign-item-value-row">
        <div
            class="hc-campaign-item-value-row-label"
            v-text="'Document à générer'"
        ></div>
        <div class="hc-campaign-item-value-row-select">
            <select v-model="document">
                <option
                    v-for="document in prospectDocuments"
                    :key="document.id"
                    :value="document.id"
                    v-text="document.name"
                ></option>
            </select>
        </div>
    </label>
    <label class="hc-campaign-item-value-row">
        <div
            class="hc-campaign-item-value-row-label"
            v-text="'Envoyer dans ...'"
        ></div>
        <div class="hc-campaign-item-value-row-select">
            <select v-model="thread">
                <option
                    v-for="thread in threads"
                    :key="thread.id"
                    :value="thread.id"
                    v-text="thread.name"
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
        ...mapGetters(["threads", "documents"]),

        prospectDocuments() {
            return this.documents.filter(
                (document) => document.for == "prospect"
            );
        },

        ...Object.fromEntries(
            ["document", "thread"].map((key) => [
                key,
                {
                    // Get selected field CSS value
                    get() {
                        return this.action.value[key]
                            ? this.action.value[key]
                            : null;
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
            ])
        ),
    },
};
</script>
