<template>
    <item tag="label">
        <icon :class="icon" :style="style" />
        <div class="hc-item-main-content" v-text="calendar.name"></div>
        <icon
            v-if="can('all.project.calendar.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent="edit"
        />
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_MODAL } from "@/actions/modal";
import { SET_CALENDAR } from "@/actions/project/calendar";

export default {
    props: {
        modelValue: {
            type: Array,
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
            } else {
                newValue.splice(newValue.indexOf(this.value), 1);
            }
            this.$emit("update:modelValue", newValue);
        },

        edit() {
            store.commit(OPEN_MODAL, "calendar-update");
            store.commit(SET_CALENDAR, this.calendar);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        value() {
            return this.calendar.id;
        },

        /**
         *
         */
        isChecked() {
            return this.modelValue.includes(this.value);
        },

        /**
         *
         */
        style() {
            return {
                color: this.calendar.bgcolor,
            };
        },

        /**
         *
         */
        icon() {
            if (this.calendar.type == "physical") {
                return "fa fa-map-marker";
            } else if (this.calendar.type == "telephone") {
                return "fa fa-phone";
            } else if (this.calendar.type == "hangout") {
                return "fa fa-video";
            } else if (this.calendar.type == "task") {
                return "fa fa-task";
            } else {
                return "fa fa-calendar";
            }
        },
    },
};
</script>
