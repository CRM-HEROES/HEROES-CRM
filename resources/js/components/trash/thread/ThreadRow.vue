<template>
    <item class="hc-trash-thread" tag="label">
        <icon class="fa fa-envelope" :color="thread.bgcolor" />
        <div class="hc-item-main-content hc-flex-column">
            <div class="hc-trash-thread-name" v-text="thread.name"></div>
            <span class="hc-trash-thread-date" v-text="date"></span>
        </div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<style>
.hc-trash-thread {
    padding: 2px 0 !important;
}

.hc-trash-thread-name {
    color: #000000;
    white-space: normal;
}

.hc-trash-thread-date {
    color: #777777;
    font-size: 11px;
}
</style>

<script>
export default {
    props: {
        modelValue: {
            type: Array,
        },

        thread: {
            type: Object,
        },
    },

    methods: {
        change(event) {
            let isChecked = event.target.checked;
            let newValue = [...this.modelValue];
            if (isChecked) {
                newValue.push(this.value);
            } else {
                newValue.splice(newValue.indexOf(this.value), 1);
            }
            this.$emit("update:modelValue", newValue);
        },
    },

    computed: {
        /**
         *
         */
        date() {
            return "Supprimé " + dayjs(this.thread.deleted_at).fromNow();
        },

        /**
         *
         */
        value() {
            return this.thread.id;
        },

        /**
         *
         */
        isChecked() {
            return this.modelValue.includes(this.value);
        },
    },
};
</script>
