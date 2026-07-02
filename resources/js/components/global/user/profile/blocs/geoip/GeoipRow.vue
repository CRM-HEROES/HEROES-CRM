<template>
    <div class="hc-user-geoip">
        <div class="hc-user-geoip-date" v-text="date"></div>
        <div class="hc-user-geoip-location" v-text="location"></div>
        <div class="hc-user-geoip-ip-address" v-text="geoip.ip"></div>
        <a
            class="hc-user-geoip-lat-lng"
            v-text="latLng"
            :href="googleMapURL"
            target="_blank"
        ></a>
    </div>
</template>

<style>
.hc-user-geoip {
    display: flex;
    flex-direction: column;
    margin-left: 16px;
    padding-left: 24px;
    padding-bottom: 16px;
    position: relative;
    border-left: 1px solid #ddd;
}

.hc-user-geoip:before {
    content: "";
    display: inline-block;
    position: absolute;
    background-color: #ddd;
    width: 12px;
    height: 12px;
    border-radius: 1000px;
    top: 4px;
    left: -6px;
}

.hc-user-geoip-date {
    font-size: 12px;
}

.hc-user-geoip-location {
    font-size: 13px;
    font-weight: bold;
}

.hc-user-geoip-ip-address {
    font-size: 12px;
}

.hc-user-geoip-lat-lng {
    font-size: 12px;
}
</style>

<script>
export default {
    props: {
        geoip: {
            type: Object,
        },
    },

    computed: {
        date() {
            return this.geoip.to
                ? dayjs(this.geoip.to).format("ddd, DD MMM YYYY")
                : "Maintenant";
        },

        location() {
            return [this.geoip.city, this.geoip.country_name]
                .filter((e) => e)
                .join(" ");
        },

        latLng() {
            return `(${this.geoip.latitude}, ${this.geoip.longitude})`;
        },

        googleMapURL() {
            return `http://maps.google.com/maps?q=${this.geoip.latitude},${this.geoip.longitude}`;
        },
    },
};
</script>
