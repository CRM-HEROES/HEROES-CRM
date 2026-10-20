<template>
    <div
        :class="[
            'hc-prospect-sms',
            'hc-flex-row',
            isIncoming ? 'hc-prospect-sms-from-prospect' : '',
        ]"
    >
        <div class="hc-prospect-sms-content hc-flex-column">
            <div
                class="hc-prospect-sms-header"
                v-if="sms.creator"
                v-text="sms.creator.name"
            ></div>
            <div v-if="sms.message" class="hc-prospect-sms-message" v-text="sms.message"></div>
            <div v-if="sms.attachments && sms.attachments.length" class="hc-prospect-sms-media">
                <a
                    v-for="attachment in sms.attachments"
                    :key="attachment.url"
                    :href="attachment.url"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <img v-if="isImageAttachment(attachment)" :src="attachment.url" :alt="attachment.file_name" loading="lazy" />
                    <span v-text="attachment.file_name"></span>
                </a>
            </div>
            <div class="hc-prospect-sms-footer" v-text="footer"></div>
            <div
                class="hc-prospect-sms-error"
                v-if="sms.error"
                v-text="sms.error"
            ></div>
        </div>
    </div>
</template>

<style>
.hc-prospect-sms {
    justify-content: end;
}

.hc-prospect-sms-content {
    align-items: end;
    width: 100%;
}

.hc-prospect-sms-from-prospect .hc-prospect-sms-content {
    align-items: start;
}

.hc-prospect-sms-content > * {
    max-width: 90%;
    white-space: normal;
}

.hc-prospect-sms-header {
    font-size: 11px;
    color: #999999;
}

.hc-prospect-sms-message {
    background-color: #7939b8;
    color: #e6deef;
    padding: 7px 10px;
    border-radius: 15px;
    box-shadow: 0 3px 20px -3px #0005;
    position: relative;
    font-size: 11px;
}

.hc-prospect-sms-media {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.hc-prospect-sms-media a {
    display: flex;
    flex-direction: column;
    max-width: 120px;
    overflow-wrap: anywhere;
    font-size: 11px;
}

.hc-prospect-sms-media img {
    width: 120px;
    height: 100px;
    max-width: 120px;
    max-height: 120px;
    object-fit: contain;
    border-radius: 6px;
}

.hc-prospect-sms-from-prospect .hc-prospect-sms-message {
    background-color: #eeeeee;
    color: #333333;
    border-radius: 7px 7px 7px 0;
    text-align: left;
}

.hc-prospect-sms-error {
    color: #b75959;
    font-size: 11px;
    text-align: right;
}

.hc-prospect-sms-footer {
    font-size: 11px;
    color: #999999;
    text-align: right;
    margin-top: 5px;
}
</style>

<script>
export default {
    props: {
        sms: {
            type: Object,
        },
    },

    methods: {
        isImageAttachment(attachment) {
            return ["image/jpeg", "image/png", "image/gif"].includes(attachment.mime_type)
                || ["jpg", "jpeg", "png", "gif"].includes((attachment.extension || "").toLowerCase());
        },
    },

    computed: {
        /**
         *
         */
        footer() {
            const direction = this.isIncoming ? " entrant" : "";

            return (
                dayjs(this.sms.created_at).fromNow() +
                " via " +
                this.sms.source +
                direction
            );
        },

        /**
         *
         */
        isIncoming() {
            return this.sms.is_incoming || !this.sms.from_user;
        },
    },
};
</script>
