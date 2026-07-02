<template>
    <th class="hc-user-agenda-month-table-header-cell">
        <span v-text="dateLabel"></span>
    </th>
</template>

<style>
.hc-user-agenda-month-table-header-cell {
    height: 30px;
    padding-top: 7px;
    font-weight: normal;
}
</style>

<script>
export default {
    props: {
        day: {
            type: Number,
        },
    },

    data() {
        return {
            days: ["DIM", "LUN", "MAR", "MER", "JEU", "VEN", "SAM"],
        };
    },

    computed: {
        dateLabel() {
            return this.days[this.day];
        },
    },
};
</script>
