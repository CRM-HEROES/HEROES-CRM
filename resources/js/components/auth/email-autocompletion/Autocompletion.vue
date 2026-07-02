<template>
    <div
        id="hc-login-email-autocompletion"
        :class="showAutocompletion ? '' : 'hide'"
    >
        <autocompletion-row
            v-for="(item, i) in filteredItems"
            :key="item.key"
            :email="item.value"
            :selected="i == selected"
            @mouseenter="selected = i"
            @click.stop="select(item.value)"
        />
    </div>
</template>

<style>
#hc-login-email-autocompletion {
    position: absolute;
    width: 100%;
    border: 1px solid #eee;
    background-color: white;
    z-index: 1;
}
#hc-login-email-autocompletion.hide {
    display: none;
}
</style>

<script>
import AutocompletionRow from "./AutocompletionRow.vue";

export default {
    components: {
        AutocompletionRow,
    },

    props: {
        /**
         *
         */
        modelValue: {
            type: String,
            default: "",
        },
        /**
         *
         */
        show: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            items: [
                "@gmail.com",
                "@yahoo.com",
                "@outlook.com",
                "@icloud.com",
                "@aol.com",
            ],
            showAutocompletion: false,
            selected: -1,
        };
    },

    methods: {
        select(email) {
            this.$emit("update:modelValue", email);
            this.showAutocompletion = false;
        },

        /**
         *
         * @param {*} e
         */
        selectAutocomplete(e) {
            if (this.filteredItems.length > 0) {
                // Down
                if (e.keyCode == 40) {
                    e.preventDefault();

                    if (this.selected + 1 < this.filteredItems.length) {
                        this.selected++;
                    } else {
                        this.selected = -1;
                    }

                    return;
                    // Up
                } else if (e.keyCode == 38) {
                    e.preventDefault();

                    if (this.selected <= -1) {
                        this.selected = this.filteredItems.length - 1;
                    } else {
                        this.selected--;
                    }

                    return;
                    // Enter
                } else if (e.keyCode == 13) {
                    this.select(this.filteredItems[this.selected].value);
                }
            }
        },
    },

    watch: {
        modelValue(newValue) {
            this.showAutocompletion =
                this.modelValue && this.filteredItems.length > 0;
        },
        filteredItems(newValue) {
            this.showAutocompletion =
                this.modelValue && this.filteredItems.length > 0;
        },
        show(newValue) {
            this.showAutocompletion = newValue;
        },
    },

    computed: {
        filteredItems() {
            const index = this.modelValue.indexOf("@");
            if (index < 0) {
                return this.items.map((i) => ({
                    key: i,
                    value: this.modelValue + i,
                }));
            }

            const e = this.modelValue.substring(0, index);
            const key = this.modelValue.substring(index);
            return this.items
                .filter((i) => i.indexOf(key) >= 0 && key != i)
                .map((i) => ({
                    key: i,
                    value: e + i,
                }));
        },
    },
};
</script>
