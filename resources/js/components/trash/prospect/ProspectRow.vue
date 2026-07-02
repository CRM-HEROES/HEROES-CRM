<template>
    <item class="hc-trash-prospect" tag="label">
        <icon class="fa fa-user" />
        <div class="hc-item-main-content hc-flex-column">
            <div class="hc-trash-prospect-name" v-text="name"></div>
            <span class="hc-trash-prospect-date" v-text="date"></span>
        </div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<style>
.hc-trash-prospect {
    padding: 2px 0 !important;
}

.hc-trash-prospect-name {
    font-weight: 500;
}

.hc-trash-prospect-date {
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

        prospect: {
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
            return "Supprimé " + dayjs(this.prospect.deleted_at).fromNow();
        },

        /**
         *
         */
        name() {
            return [this.prospect.first_name, this.prospect.last_name].join(
                " "
            );
        },

        /**
         *
         */
        value() {
            return this.prospect.id;
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
