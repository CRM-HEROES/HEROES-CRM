<template>
    <item>
        <icon :class="['fa', icon]" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="calendar.name"></div>
        <div
            v-if="calendar.project"
            class="hc-global-user-profile-item-project"
            v-text="calendar.project.name"
        ></div>
    </item>
</template>

<script>
export default {
    props: {
        calendar: {
            type: Object,
        },
    },

    computed: {
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
                return "fa fa-tasks";
            } else {
                return "fa fa-calendar";
            }
        },
    },
};
</script>
