<template>
    <div :id="id" class="hc-kavkom-shell">

        <iframe
            v-if="kavkomUrl"
            :key="kavkomUrl"
            class="hc-kavkom-iframe"
            :src="kavkomUrl"
            title="Kavkom"
            loading="eager"
            referrerpolicy="strict-origin-when-cross-origin"
            allow="microphone; camera; autoplay"
            @load="onIframeLoaded"
            @error="onIframeError"
        ></iframe>

        <div v-else class="hc-kavkom-fallback">
            Chargement de l’interface Kavkom…
        </div>

        <div v-if="iframeError" class="hc-kavkom-fallback-message">
            L’interface Kavkom n’a pas pu être chargée directement ici. Vous pouvez utiliser le bouton ci-dessus pour l’ouvrir dans un onglet.
        </div>
    </div>
</template>

<style>
.hc-kavkom-shell {
    display: flex;
    flex-direction: column;
    gap: 10px;
    height: 100%;
    min-height: 480px;
}
.hc-kavkom-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}
.hc-kavkom-title {
    font-weight: 600;
    color: #343a40;
}
.hc-kavkom-subtitle {
    font-size: 12px;
    color: #6c757d;
}
.hc-kavkom-open-btn {
    border: 0;
    border-radius: 6px;
    background: #8e24aa;
    color: #fff;
    padding: 8px 12px;
    cursor: pointer;
    font-size: 13px;
}
.hc-kavkom-iframe {
    flex: 1;
    width: 100%;
    min-height: 420px;
    border: 0;
    border-radius: 6px;
    background: #fff;
}
.hc-kavkom-fallback,
.hc-kavkom-fallback-message {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 120px;
    padding: 16px;
    border: 1px dashed #d0d7de;
    border-radius: 8px;
    background: #f8f9fa;
    color: #6c757d;
    font-size: 13px;
    text-align: center;
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

        options: {
            type: Object,
            default: {
                animation: false,
                size: "auto",
                type: "relative",
            },
        },
    },

    data() {
        return {
            kavkomUrl: null,
            iframeError: false,
        };
    },

    mounted() {
        this.kavkomUrl = this.buildKavkomUrl();
    },

    methods: {
        normalizeNumber(value) {
            if (!value) {
                return "";
            }

            return String(value).replace(/[^\d+]/g, "");
        },

        buildKavkomUrl() {
            const baseUrl = "https://app.kavkom.com/";
            const url = new URL(baseUrl);
            const normalizedNumber = this.normalizeNumber(this.number);

            if (normalizedNumber) {
                url.searchParams.set("phone", normalizedNumber);
                url.searchParams.set("number", normalizedNumber);
            }

            url.searchParams.set("language", "french");

            return url.toString();
        },

        openInNewTab() {
            if (this.kavkomUrl) {
                window.open(this.kavkomUrl, "_blank", "noopener,noreferrer");
            }
        },

        onIframeLoaded() {
            this.iframeError = false;
        },

        onIframeError() {
            this.iframeError = true;
        },
    },

    watch: {
        number() {
            this.iframeError = false;
            this.kavkomUrl = this.buildKavkomUrl();
        },
    },
};
</script>
