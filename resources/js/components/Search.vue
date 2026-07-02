<template>
    <div class="hc-search">
        <div
            class="hc-search-content"
            :style="width ? { width: width + 'px' } : {}"
        >
            <input
                v-model="value"
                :placeholder="placeholder ? placeholder : $t('search') + ' ...'"
            />
            <a
                class="hc-search-init fa fa-times"
                @click.prevent="value = ''"
            ></a>
        </div>
    </div>
</template>

<style>
.hc-search {
    border-bottom: 1px solid #eeeeee;
    padding: 10px;
    width: 100%;
    position: relative;
    text-align: center;
}

.hc-search-content {
    height: auto;
    width: 100%;
    max-width: 100%;
    margin: auto;
    display: inline-block;
    position: relative;
}

.hc-search-content > input {
    background-color: #f5f4f5;
    border: none;
    font-size: 12px;
    height: 40px;
    padding: 10px 17px;
    width: 100%;
    border-radius: 20px;
    display: inline-block;
}

.hc-search-content > input:focus {
    outline: 2px solid #1e88e5;
}

.hc-search-init {
    width: 40px;
    height: 40px;
    position: absolute;
    top: 0;
    right: 0;
    font-size: 13px;
    display: inline-block;
    line-height: 40px;
    text-align: center;
    text-decoration: none;
    color: #999;
    cursor: pointer;
}

.hc-search-init:hover {
    text-decoration: none;
    color: #777;
}

.dark .hc-search-content {
    border-bottom-color: #444444;
}

.dark .hc-search-content > input {
    background-color: #343634;
    border: 1px solid #444444;
}
</style>

<script>
export default {
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
        placeholder: {
            type: String,
            default: null,
        },

        /**
         *
         */
        width: {
            type: Number,
            default: null,
        },
    },

    data() {
        return {
            value: this.modelValue,
        };
    },

    watch: {
        /**
         *
         */
        modelValue(newValue) {
            this.value = newValue;
        },

        /**
         *
         */
        value(newValue) {
            this.$emit("update:modelValue", newValue);
        },
    },
};
</script>
