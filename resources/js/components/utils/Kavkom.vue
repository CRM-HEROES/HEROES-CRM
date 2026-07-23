<template>
    <div :id="id" class="hc-kavkom-shell">
        <iframe
            :key="kavkomUrl"
            class="hc-kavkom-iframe"
            :src="kavkomUrl"
            title="Kavkom"
            loading="eager"
            referrerpolicy="strict-origin-when-cross-origin"
            allow="microphone; camera; autoplay"
        ></iframe>
    </div>
</template>

<style>
.hc-kavkom-shell {
    display: flex;
    flex-direction: column;
    height: 100%;
    min-height: 480px;
}
.hc-kavkom-iframe {
    flex: 1;
    width: 100%;
    min-height: 420px;
    border: 0;
    border-radius: 6px;
    background: #fff;
}
</style>

<script>
export default {
    props: {
        id: {
            type: String,
        },

        number: {
            type: String,
            default: null,
        },

        tab: {
            type: String,
            default: "phone",
        },
    },

    data() {
        return {
            kavkomSettings: null,
        };
    },

    async mounted() {
        await this.loadUserKavkomSettings();
        this.kavkomSettings = this.getKavkomSettings();
    },

    methods: {
        normalizeNumber(value) {
            if (!value) {
                return "";
            }

            return String(value).replace(/[^\d+]/g, "");
        },

        async loadUserKavkomSettings() {
            try {
                if (this.$store && this.$store.dispatch) {
                    await this.$store.dispatch("getUserSetting", "kavkom");
                }
            } catch (e) {
                // ignore, iframe falls back to the Kavkom login page
            }
        },

        getKavkomSettings() {
            try {
                if (
                    this.$store &&
                    this.$store.getters &&
                    this.$store.getters.userSettings
                ) {
                    return this.$store.getters.userSettings["kavkom"] || null;
                }

                return null;
            } catch (e) {
                return null;
            }
        },
    },

    computed: {
        kavkomUrl() {
            const baseUrl = "https://app.kavkom.com/";
            const url = new URL(baseUrl);
            const normalizedNumber = this.normalizeNumber(this.number);

            if (normalizedNumber) {
                url.searchParams.set("phone", normalizedNumber);
                url.searchParams.set("number", normalizedNumber);
            }

            url.searchParams.set("language", "french");

            const settings = this.kavkomSettings;

            if (settings && settings.domain_uuid) {
                url.searchParams.set("domain_uuid", settings.domain_uuid);
            }

            return url.toString();
        },
    },

    watch: {
        "$store.getters.userSettings"() {
            this.kavkomSettings = this.getKavkomSettings();
        },
    },
};
</script>
