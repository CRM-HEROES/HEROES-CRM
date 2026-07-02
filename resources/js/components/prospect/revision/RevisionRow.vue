<template>
    <item class="hc-prospect-revision" tag="label">
        <icon :class="['fa', icon]" />
        <div class="hc-item-main-content hc-flex-column">
            <div class="hc-flex-row">
                <div
                    class="hc-prospect-revision-user"
                    v-text="revision.user ? revision.user.name : ''"
                ></div>
                <div class="hc-prospect-revision-date" v-text="hour"></div>
            </div>
            <div class="hc-prospect-revision-value hc-flex-row">
                <div
                    class="hc-prospect-revision-field"
                    v-text="revision.field + ' : '"
                ></div>
                <div
                    v-if="revision.old_value"
                    class="hc-prospect-revision-old-value"
                    v-text="revision.old_value"
                ></div>
                <div
                    class="hc-prospect-revision-new-value"
                    v-text="revision.new_value"
                ></div>
            </div>
        </div>
    </item>
</template>

<style>
.hc-prospect-revision {
    padding: 2px 0 !important;
}

.hc-prospect-revision-value {
    flex-wrap: wrap;
}

.hc-prospect-revision-value > * {
    white-space: normal;
}

.hc-prospect-revision-user {
    flex: 1;
}

.hc-prospect-revision-date {
    text-align: right;
    opacity: 0.8;
}

.hc-prospect-revision-field {
    font-weight: 700;
    color: #1e6ee5;
    margin-right: 5px;
}

.hc-prospect-revision-old-value {
    margin-right: 20px;
    position: relative;
}

.hc-prospect-revision-old-value:after {
    content: "";
    position: absolute;
    right: -14px;
    top: 5px;
    width: 0;
    height: 0;
    border-radius: 2px;
    border-left: 5px solid #aaa;
    border-top: 4px solid transparent;
    border-bottom: 4px solid transparent;
}
</style>

<script>
export default {
    props: {
        revision: {
            type: Object,
        },
    },

    computed: {
        /**
         *
         */
        icon() {
            if (this.revision.type == "label") {
                return "fa-tags";
            }

            return "fa-pen";
        },

        /**
         *
         */
        hour() {
            return dayjs(this.revision.created_at).format("HH:mm");
        },
    },
};
</script>
