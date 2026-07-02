<template>
    <item>
        <icon class="fa fa-calendar" />
        <div class="hc-item-main-content" v-text="date"></div>
        <div class="hc-item-count" v-text="stat.connection"></div>
        <icon class="fa fa-caret-right" />
    </item>
</template>

<script>
export default {
    props: {
        stat: {
            stat: Object,
        },
    },

    computed: {
        date: function () {
            const [year, month] = this.stat.month.split("-");

            return month + " / " + year;
        },
    },
};
</script>
