<template>
    <div :class="['hc-search-dropdown']">
        <div class="hc-search-dropdown-input-container">
            <input
                class="hc-search-dropdown-input"
                v-model="keyword"
                :placeholder="placeholder"
            />
        </div>
        <div class="hc-search-dropdown-result-container">
            <div
                v-for="item in result"
                :key="itemKey(item.item, item.arrIndex)"
                class="hc-search-dropdown-result-item"
                @click.stop="selectItem(item)"
            >
                <slot name="item" :item="item.item" :arr-index="item.arrIndex">
                    <div
                        class="hc-search-dropdown-result-item-default"
                        v-text="itemLabel(item.item)"
                    ></div>
                </slot>
            </div>
        </div>
    </div>
</template>

<style>
.hc-search-dropdown {
    width: 240px;
    height: 34px;
    position: relative;
    border-radius: 7px;
    background-color: #ebebeb;
    overflow: visible !important;
}

.hc-search-dropdown-input-container {
    width: 100%;
    height: 100%;
}

.hc-search-dropdown-input {
    border: none;
    font-size: 12px;
    height: 100%;
    padding: 0 10px;
    width: 100%;
    background: none;
}

.hc-search-dropdown-result-container {
    width: 100%;
    height: auto;
    position: relative;
    left: 0;
    background-color: #fff;
    border: 1px solid #eee;
    z-index: 1;
}

.hc-search-dropdown-result-container:empty {
    display: none;
}

.hc-search-dropdown-result-item {
    width: 100%;
}

.hc-search-dropdown-result-item-default {
    height: 24px;
    line-height: 24px;
    padding: 0 8px;
    font-size: 12px;
    cursor: pointer;
}

.hc-search-dropdown-result-item-default:hover {
    background-color: #eee;
}
</style>

<script>
export default {
    props: {
        items: {
            type: Array,
        },
        maxResult: {
            type: Number,
        },
        placeholder: {
            type: String,
            default: "Chercher ...",
        },
        itemKey: {
            type: Function,
            default: (item, index) => index + "-" + item.id,
        },
        itemLabel: {
            type: Function,
            default: (item) => item.name,
        },
        itemSearch: {
            type: Function,
            default: (item, keyword) =>
                removeStringAccent(item.name).indexOf(keyword) >= 0,
        },
    },

    data() {
        return {
            keyword: "",
        };
    },

    methods: {
        selectItem(item) {
            this.$emit("item-selected", item.item, item.arrIndex);
            this.keyword = "";
        },
    },

    watch: {
        keyword(newValue) {
            this.$emit("change", newValue);
        },
    },

    computed: {
        /**
         */
        result() {
            if (this.keyword === "") {
                return [];
            }

            const keyword = removeStringAccent(this.keyword);

            const result = this.items.reduce(
                (carry, arr, arrIndex) => [
                    ...carry,
                    ...arr
                        .filter((item) => this.itemSearch(item, keyword))
                        .map((item) => ({
                            item,
                            arrIndex,
                        })),
                ],
                []
            );

            return this.maxResult ? result.slice(0, this.maxResult) : result;
        },
    },
};
</script>
