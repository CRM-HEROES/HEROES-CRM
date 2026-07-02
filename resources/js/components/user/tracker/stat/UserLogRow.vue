<template>
    <div class="user-log-row">
        <div :class="'user-log-url-method method-' + log.method.toLowerCase()">
            <span v-text="log.method"></span>
        </div>

        <div class="user-log-info">
            <div
                class="user-log-url"
                v-if="log.route_path && log.route_path.path"
            >
                <span
                    class="user-log-url-link"
                    v-text="log.route_path.path"
                ></span>
            </div>

            <div class="user-log-device">
                <div
                    class="user-log-plateform"
                    v-if="
                        log.session &&
                        log.session.device &&
                        log.session.device.platform
                    "
                >
                    <i class="fa fa-mobile"></i>
                    <span v-text="log.session.device.platform"></span>
                </div>
                <div
                    class="user-log-browser"
                    v-if="
                        log.session &&
                        log.session.agent &&
                        log.session.agent.browser
                    "
                >
                    <i class="fa fa-globe"></i>
                    <span v-text="log.session.agent.browser"></span>
                </div>
                <div
                    class="user-log-ip"
                    v-if="log.session && log.session.client_ip"
                >
                    <i class="fa fa-address-book"></i>
                    <span v-text="log.session.client_ip"></span>
                </div>
                <div class="user-log-date">
                    <i class="fa fa-calendar"></i>
                    <span v-text="date"></span>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.user-log-row {
    width: 100%;
    border-bottom: 1px solid #4e13ad11;
    padding: 5px 15px 5px 5px;
    display: flex;
    float: left;
    flex-direction: row;
}

.user-log-row:hover {
    text-decoration: none;
    background-color: #f5f5f5;
}

.user-log-url {
    display: flex;
    flex-direction: row;
}

.user-log-url-method {
    width: 50px;
    height: 40px;
    text-align: center;
    line-height: 40px;
}

.user-log-url-method > span {
    display: inline-block;
    border-radius: 2px;
    padding: 0 4px;
    color: white;
    font-size: 11px;
    height: 20px;
    line-height: 20px;
}

.user-log-url-method.method-get > span {
    background-color: #489f1f;
}

.user-log-url-method.method-post > span {
    background-color: #1e6ee5;
}

.user-log-url-method.method-put > span,
.user-log-url-method.method-patch > span {
    background-color: #159eec;
}

.user-log-url-method.method-delete > span {
    background-color: #ed5e3e;
}

.user-log-url-link {
    flex: 1;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}

.user-log-date {
    color: #555;
    white-space: nowrap;
}

.user-log-info {
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding-right: 10px;
}

.user-log-info > div {
    width: 100%;
}

.user-log-device {
    color: #777;
}

.user-log-device > div {
    display: inline-block;
    margin-right: 5px;
}

.user-log-browser,
.user-log-ip,
.user-log-date {
    margin-right: 10px;
}

.user-log-browser > i,
.user-log-ip > i,
.user-log-date > i {
    margin-right: 5px;
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
         */
        date() {
            const date = dayjs(new Date(this.log.created_at)).format(
                "DD MMM YYYY HH:mm"
            );

            if (date == "Invalid date") {
                return this.log.created_at;
            }

            return date;
        },
    },
};
</script>
