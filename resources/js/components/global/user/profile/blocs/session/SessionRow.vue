<template>
    <item :class="['hc-user-session', session.banned_at ? 'banned' : '']">
        <icon :class="['fa', icon]" />
        <div class="hc-item-main-content hc-flex-column">
            <div class="hc-flex-row">
                <div
                    v-text="platform"
                    class="hc-flex-1 hc-user-session-platform"
                ></div>
                <div v-text="date" class="hc-user-session-date"></div>
            </div>
            <a
                class="hc-user-session-address"
                :href="addressUrl"
                target="_blank"
                v-text="address"
            ></a>
        </div>
        <!--icon
            v-if="session.banned_at"
            class="fa fa-hand-paper"
            tag="a"
            @click.prevent="cancelBan"
            color="#00b01e"
        />
        <icon
            v-else
            class="fa fa-ban"
            tag="a"
            @click.prevent="ban"
            color="#ff2121"
        /-->
    </item>
</template>

<style>
.hc-user-session {
    padding: 2px 0 !important;
}

.hc-user-session.banned {
    background-color: #ffe6e6;
}

.hc-user-session-platform {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    padding-right: 10px;
}

.hc-user-session-date {
    opacity: 0.8;
    font-size: 11px;
}

.hc-user-session-address {
    text-decoration: none;
    color: #12a0f3;
    white-space: initial;
}
</style>

<script>
import store from "@/store";

export default {
    props: {
        session: {
            type: Object,
        },
    },

    methods: {
        cancelBan() {
            this.$emit("canceling-device", this.session);
        },

        ban() {
            this.$emit("banning-device", this.session);
        },
    },

    computed: {
        /**
         *
         */
        icon() {
            if (this.session.kind == "Computer") {
                return "fa-desktop";
            }

            if (this.session.kind == "Tablet") {
                return "fa-tablet";
            }

            return "fa-mobile";
        },

        /**
         *
         */
        platform() {
            return [
                this.session.model,
                this.session.platform,
                this.session.platform_version.replace(/_/g, "."),
            ].join(" ");
        },

        /**
         *
         */
        address() {
            return [
                this.session.country_code,
                this.session.country_name,
                this.session.postal_code,
                this.session.city,
                this.session.region,
            ].join(" ");
        },

        /**
         *
         */
        addressUrl() {
            return `https://www.google.com/maps?q=${this.session.latitude},${this.session.longitude}`;
        },

        /**
         *
         */
        date() {
            return dayjs(this.session.created_at).fromNow();
        },
    },
};
</script>
