<template>
    <label class="hc-campaign-item-value-row">
        <div class="hc-campaign-item-value-row-label" v-text="'Filtre'"></div>
        <div class="hc-campaign-item-value-row-select">
            <select v-model="categoryId">
                <option
                    v-for="category in categories"
                    :key="category.id"
                    :value="category.id"
                    v-text="category.name"
                ></option>
            </select>
        </div>
    </label>
    <label v-if="category" class="hc-campaign-item-value-row">
        <div
            class="hc-campaign-item-value-row-label"
            v-text="'Sous-filtre'"
        ></div>
        <div class="hc-campaign-item-value-row-select">
            <select
                v-model="labelId"
                :style="
                    label
                        ? { backgroundColor: label.bgcolor, color: label.color }
                        : {}
                "
            >
                <option
                    v-for="label in category.labels"
                    :key="label.id"
                    :value="label.id"
                    v-text="label.name"
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
        ...mapGetters(["categories"]),

        category() {
            if (!this.action.value || !this.action.value.label) {
                return null;
            }

            return this.categories.find((c) =>
                c.labels.find((l) => l.id == this.action.value.label)
            );
        },

        categoryId: {
            get() {
                return this.category ? this.category.id : null;
            },
            set(value) {
                const category = this.categories.find((c) => c.id == value);
                if (!category) {
                    return;
                }

                if (category.labels.length <= 0) {
                    return;
                }

                this.labelId = category.labels[0].id;
            },
        },

        label() {
            if (!this.category) {
                return null;
            }

            return this.category.labels.find(
                (l) => l.id == this.action.value.label
            );
        },

        labelId: {
            get() {
                return this.action.value ? this.action.value.label : null;
            },
            set(value) {
                store.dispatch(UPDATE_CAMPAIGN_ACTION, {
                    id: this.action.id,
                    value: {
                        ...this.action.value,
                        label: value,
                    },
                });
            },
        },
    },
};
</script>
