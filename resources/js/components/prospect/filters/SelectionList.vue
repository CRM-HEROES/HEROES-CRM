<template>
    <template v-if="items.length || renderWhenEmpty">
        <item tag="label" class="hc-prospect-filter-selection-list">
            <icon :class="showSelectAll ? selectAllIcon : 'fa fa-sort'" />
            <div
                v-if="showSelectAll"
                class="hc-item-main-content"
                v-text="$t('prospect.table.filters.selection.select_all')"
            ></div>
            <div
                v-else
                class="hc-item-main-content"
                v-text="$t('prospect.table.filters.selection.sort_items')"
            ></div>
            <icon
                v-if="sortEnabled"
                tag="a"
                :class="sortIcon"
                :title="sortTitle"
                @click.prevent.stop="toggleSort"
            />
            <checkbox
                v-if="showSelectAll"
                :model-value="allSelected"
                :disabled="disabled"
                @update:model-value="selectAll"
            />
        </item>
        <slot :sorted-items="sortedItems" />
    </template>
</template>

<script>
export default {
    emits: ["select-all", "update:modelValue"],

    props: {
        items: {
            type: Array,
            default: () => [],
        },
        isSelected: {
            type: Function,
            default: null,
        },
        selected: {
            type: Array,
            default: () => [],
        },
        valueKey: {
            type: String,
            default: "id",
        },
        modelValue: {
            default: undefined,
        },
        selectAllChecked: {
            default: undefined,
        },
        showSelectAll: {
            type: Boolean,
            default: true,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        renderWhenEmpty: {
            type: Boolean,
            default: false,
        },
        sortEnabled: {
            type: Boolean,
            default: true,
        },
        selectAllIcon: {
            type: String,
            default: "fa fa-check-square",
        },
    },

    data() {
        return {
            selectedFirst: true,
        };
    },

    computed: {
        allSelected() {
            if (this.modelValue !== undefined) {
                return !!this.modelValue;
            }

            if (this.selectAllChecked !== undefined) {
                return !!this.selectAllChecked;
            }

            return (
                this.items.length > 0 &&
                this.items.every((item) => this.itemIsSelected(item))
            );
        },

        sortedItems() {
            return this.items
                .map((item, index) => ({
                    item,
                    index,
                    selected: this.itemIsSelected(item),
                }))
                .sort((left, right) => {
                    if (left.selected !== right.selected) {
                        const selectedOrder = left.selected ? -1 : 1;
                        return this.selectedFirst
                            ? selectedOrder
                            : -selectedOrder;
                    }

                    return left.index - right.index;
                })
                .map(({ item }) => item);
        },

        sortIcon() {
            return this.selectedFirst
                ? "fa fa-sort-amount-down"
                : "fa fa-sort-amount-up";
        },

        sortTitle() {
            return this.$t(
                this.selectedFirst
                    ? "prospect.table.filters.selection.unselected_first"
                    : "prospect.table.filters.selection.selected_first"
            );
        },
    },

    methods: {
        itemIsSelected(item) {
            if (this.isSelected) {
                return this.isSelected(item);
            }

            const value = item && item[this.valueKey];

            return this.selected.some((selected) => {
                const selectedValue =
                    selected && typeof selected === "object"
                        ? selected[this.valueKey]
                        : selected;

                return selectedValue == value;
            });
        },

        selectAll(checked) {
            this.$emit("select-all", checked);
            this.$emit("update:modelValue", checked);
        },

        toggleSort() {
            this.selectedFirst = !this.selectedFirst;
        },
    },
};
</script>

<style>
.hc-prospect-filter-selection-list > .fa-sort-amount-down,
.hc-prospect-filter-selection-list > .fa-sort-amount-up {
    cursor: pointer;
    margin-right: 8px;
}
</style>
