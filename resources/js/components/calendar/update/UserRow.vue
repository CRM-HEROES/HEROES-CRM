<template>
    <item tag="label">
        <icon class="fa fa-user" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import UserCalendarService from "@/apis/project/user/calendar";

export default {
    props: {
        modelValue: {
            type: Array,
        },

        user: {
            type: Object,
        },

        calendar: {
            type: Object,
        },
    },

    methods: {
        change(event) {
            let isChecked = event.target.checked;
            let newValue = [...this.modelValue];
            if (isChecked) {
                newValue.push(this.value);
                this.addCalendarUser();
            } else {
                newValue.splice(newValue.indexOf(this.value), 1);
                this.removeCalendarUser();
            }
            this.$emit("update:modelValue", newValue);
        },

        addCalendarUser() {
            UserCalendarService.bulkUpdate(
                this.project.slug,
                [this.user.id],
                [this.calendar.id]
            );
        },

        removeCalendarUser() {
            UserCalendarService.bulkDestroy(
                this.project.slug,
                [this.user.id],
                [this.calendar.id]
            );
        },
    },

    computed: {
        ...mapGetters(["project"]),

        /**
         *
         */
        value() {
            return this.user.id;
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
