<template>
    <item class="hc-prospect-log" tag="label">
        <icon class="fa fa-eye" />
        <div class="hc-item-main-content hc-flex-row">
            <div
                class="hc-prospect-log-user"
                v-text="log.session.user.name"
            ></div>
            <div class="hc-prospect-log-date" v-text="date"></div>
        </div>
    </item>
</template>

<style>
.hc-prospect-log {
}

.hc-prospect-log-user {
    flex: 1;
}

.hc-prospect-log-date {
}
</style>

<script>
export default {
    props: {
        log: {
            type: Object,
        },
    },

    computed: {
        /**
         *
         */
        date() {
            return dayjs(this.log.created_at).fromNow();
        },
    },
};
</script>
