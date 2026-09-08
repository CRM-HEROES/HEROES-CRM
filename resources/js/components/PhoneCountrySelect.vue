<template>
    <div class="hc-phone-country-select" ref="wrapper">
        <div
            class="hc-phone-country-trigger"
            :class="{ disabled }"
            @click.stop="!disabled && toggle()"
        >
            <span
                v-if="selected"
                v-text="`${selected.flag} ${selected.label} (+${selected.dial_code})`"
            ></span>
            <span v-else class="hc-phone-country-placeholder">—</span>
        </div>

        <teleport to="body">
            <div
                v-if="open && !disabled"
                class="hc-phone-country-panel"
                :style="panelStyle"
            >
                <input
                    ref="search"
                    type="text"
                    class="hc-phone-country-search"
                    v-model="keyword"
                    :placeholder="$t('search') + ' ...'"
                />
                <div class="hc-phone-country-list">
                    <div class="hc-phone-country-option" @click="select(null)">
                        {{ $t("none") }}
                    </div>
                    <div
                        v-for="country in filteredCountries"
                        :key="country.code"
                        class="hc-phone-country-option"
                        @click="select(country)"
                        v-text="`${country.flag} ${country.label} (+${country.dial_code})`"
                    ></div>
                </div>
            </div>
        </teleport>
    </div>
</template>

<style scoped>
.hc-phone-country-select {
    position: relative;
    display: block;
    width: 100%;
}

.hc-phone-country-trigger {
    display: block;
    width: 100%;
    box-sizing: border-box;
    padding: 2px 4px;
    font-size: 12px;
    line-height: 21px;
    cursor: pointer;
}

.hc-phone-country-trigger:hover {
    background-color: #00000011;
}

.hc-phone-country-trigger.disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

.hc-phone-country-placeholder {
    color: #999;
}
</style>

<style>
.hc-phone-country-panel {
    position: fixed;
    z-index: 9999;
    width: 280px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
    padding: 6px;
    box-sizing: border-box;
}

.hc-phone-country-search {
    width: 100%;
    height: 26px;
    padding: 0 6px;
    margin-bottom: 4px;
    font-size: 12px;
    border: 1px solid #ddd;
    border-radius: 3px;
    box-sizing: border-box;
}

.hc-phone-country-list {
    max-height: 260px;
    overflow-y: auto;
}

.hc-phone-country-option {
    padding: 5px 8px;
    font-size: 12px;
    cursor: pointer;
    border-radius: 3px;
    white-space: nowrap;
    color: #333;
}

.hc-phone-country-option:hover {
    background-color: #1e88e5;
    color: white;
}
</style>

<script>
import phoneCountries from "@/constants/phoneCountries";

export default {
    props: {
        modelValue: {
            type: String,
            default: "",
        },

        disabled: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            open: false,
            keyword: "",
            panelStyle: {},
        };
    },

    beforeUnmount() {
        document.removeEventListener("click", this.handleClickOutside, true);
        window.removeEventListener("scroll", this.handleScroll, true);
        window.removeEventListener("resize", this.close);
    },

    methods: {
        /**
         *
         */
        toggle() {
            if (this.open) {
                this.close();
                return;
            }

            const rect = this.$refs.wrapper.getBoundingClientRect();
            this.panelStyle = {
                top: `${rect.bottom + 4}px`,
                left: `${rect.left}px`,
            };
            this.keyword = "";
            this.open = true;

            this.$nextTick(() => {
                this.$refs.search?.focus();
                document.addEventListener("click", this.handleClickOutside, true);
                window.addEventListener("scroll", this.handleScroll, true);
                window.addEventListener("resize", this.close);
            });
        },

        /**
         *
         */
        close() {
            this.open = false;
            document.removeEventListener("click", this.handleClickOutside, true);
            window.removeEventListener("scroll", this.handleScroll, true);
            window.removeEventListener("resize", this.close);
        },

        /**
         * Scrolling the option list itself fires a native "scroll" event
         * that a capturing window listener also sees — only closing on a
         * scroll outside the panel keeps the list itself scrollable.
         */
        handleScroll(event) {
            if (event.target?.closest?.(".hc-phone-country-panel")) {
                return;
            }

            this.close();
        },

        /**
         *
         */
        select(country) {
            this.$emit("update:modelValue", country ? country.code : "");
            this.close();
        },

        /**
         *
         */
        handleClickOutside(event) {
            if (
                this.$refs.wrapper &&
                !this.$refs.wrapper.contains(event.target) &&
                !event.target.closest(".hc-phone-country-panel")
            ) {
                this.close();
            }
        },
    },

    computed: {
        /**
         *
         */
        selected() {
            return phoneCountries.find((c) => c.code === this.modelValue);
        },

        /**
         *
         */
        filteredCountries() {
            const keyword = removeStringAccent(this.keyword);

            return phoneCountries.filter(
                (country) =>
                    removeStringAccent(country.label).indexOf(keyword) >= 0 ||
                    country.dial_code.indexOf(keyword) >= 0
            );
        },
    },
};
</script>
