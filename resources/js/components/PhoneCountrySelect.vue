<template>
    <div class="hc-phone-country-select" ref="wrapper">
        <div class="hc-phone-country-trigger" :class="{ disabled }">
            <div class="hc-phone-country-selected-list">
                <div
                    v-for="dialCode in selectedDialCodes"
                    :key="dialCode"
                    class="hc-phone-country-tag"
                >
                    <span v-text="formatCountryTag(dialCode)"></span>
                    <icon
                        v-if="!disabled"
                        class="fa fa-times"
                        @click.stop="removeCountry(dialCode)"
                        style="cursor: pointer; margin-left: 4px"
                    />
                </div>
                <input
                    v-if="!disabled"
                    type="text"
                    class="hc-phone-country-input"
                    :placeholder="selectedDialCodes.length === 0 ? '—' : ''"
                    @click.stop="!disabled && toggle()"
                    readonly
                />
            </div>
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
                    <div
                        v-for="country in filteredCountries"
                        :key="country.code"
                        class="hc-phone-country-option"
                        :class="{ selected: isSelected(`+${country.dial_code}`) }"
                        @click="toggleCountry(country)"
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
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    width: 100%;
    box-sizing: border-box;
    padding: 2px 4px;
    font-size: 12px;
    line-height: 21px;
    cursor: pointer;
    align-items: center;
}

.hc-phone-country-trigger:hover {
    background-color: #00000011;
}

.hc-phone-country-trigger.disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

.hc-phone-country-selected-list {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    width: 100%;
    align-items: center;
}

.hc-phone-country-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background-color: #e3f2fd;
    border: 1px solid #1e88e5;
    border-radius: 3px;
    padding: 2px 6px;
    font-size: 11px;
    color: #1e88e5;
    white-space: nowrap;
}

.hc-phone-country-input {
    flex: 1;
    min-width: 100px;
    border: none;
    outline: none;
    background: transparent;
    font-size: 12px;
    cursor: pointer;
    padding: 0;
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

.hc-phone-country-option.selected {
    background-color: #1e88e5;
    color: white;
    font-weight: bold;
}
</style>

<script>
import phoneCountries from "@/constants/phoneCountries";

export default {
    props: {
        modelValue: {
            type: [String, Array],
            default: () => [],
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
         * Format country tag display
         */
        formatCountryTag(dialCode) {
            const country = phoneCountries.find((c) => c.dial_code === dialCode.replace(/^\+/, ''));
            if (!country) return dialCode;
            return `${country.flag} +${country.dial_code}`;
        },

        /**
         * Check if dial_code is selected
         */
        isSelected(dialCode) {
            return this.selectedDialCodes.includes(dialCode);
        },

        /**
         * Toggle dial_code selection (add or remove)
         */
        toggleCountry(country) {
            const dialCode = `+${country.dial_code}`;
            if (this.isSelected(dialCode)) {
                this.removeCountry(dialCode);
            } else {
                this.addCountry(dialCode);
            }
        },

        /**
         * Add dial_code to selection
         */
        addCountry(dialCode) {
            const newSelection = [...this.selectedDialCodes, dialCode];
            this.$emit("update:modelValue", newSelection);
        },

        /**
         * Remove dial_code from selection
         */
        removeCountry(dialCode) {
            const newSelection = this.selectedDialCodes.filter(
                (code) => code !== dialCode
            );
            this.$emit("update:modelValue", newSelection);
        },

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
         * Get selected dial codes as array
         */
        selectedDialCodes() {
            if (Array.isArray(this.modelValue)) {
                return this.modelValue;
            }
            return this.modelValue ? [this.modelValue] : [];
        },

        /**
         * Get the first selected dial code for backward compatibility
         */
        selected() {
            const firstDialCode = this.selectedDialCodes[0];
            if (!firstDialCode) return null;
            
            const cleanDialCode = firstDialCode.replace(/^\+/, '');
            return phoneCountries.find((c) => c.dial_code === cleanDialCode) || null;
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
