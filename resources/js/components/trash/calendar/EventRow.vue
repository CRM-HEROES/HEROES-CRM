<template>
    <item class="hc-trash-event" tag="label">
        <icon class="fa fa-calendar" :color="event.calendar.bgcolor" />
        <div class="hc-item-main-content hc-flex-column">
            <span class="hc-trash-event-name" v-text="event.name"></span>
            <span
                class="hc-trash-event-calendar"
                v-text="event.calendar ? event.calendar.name : ''"
            ></span>
            <span class="hc-trash-event-start-date" v-text="startDate"></span>
            <span class="hc-trash-event-date" v-text="date"></span>
        </div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<style>
.hc-trash-event {
    padding: 2px 0 !important;
    border-top: 1px solid #eee;
}

.hc-trash-event-calendar {
    font-size: 11px;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}

.hc-trash-event-name {
    color: #000000;
    white-space: normal;
    flex: 1;
    font-weight: 500;
}

.hc-trash-event-start-date {
    color: #12a0f3;
    font-size: 11px;
    font-weight: 500;
}

.hc-trash-event-date {
    color: #777777;
    font-size: 11px;
    margin-top: 3px;
}
</style>

<script>
import { mapGetters } from "vuex";

export default {
    props: {
        modelValue: {
            type: Array,
        },

        event: {
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
        startDate() {
            return dayjs(this.event.started_at).format(
                "ddd, DD MMM YYYY - hh:mm"
            );
        },

        /**
         *
         */
        date() {
            return "Supprimé " + dayjs(this.event.deleted_at).fromNow();
        },

        /**
         *
         */
        value() {
            return this.event.id;
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
