<template>
    <div class="hc-prospect-profile-history-row">
        <div
            class="hc-prospect-profile-history-row-icon"
            :style="{ color: color, backgroundColor: bgcolor }"
        >
            <i :class="icon"></i>
        </div>
        <div class="hc-prospect-profile-history-row-body">
            <slot :item="item"></slot>
        </div>
        <div class="hc-prospect-profile-history-row-footer">
            <div
                class="hc-prospect-profile-history-row-date"
                v-text="date"
            ></div>
            <i
                class="hc-prospect-profile-history-row-footer-separator fa fa-circle"
            ></i>
            <div
                class="hc-prospect-profile-history-row-user"
                v-if="user"
                v-text="user.name"
            ></div>
        </div>
    </div>
</template>

<style>
.hc-prospect-profile-history-row {
    display: flex;
    flex-direction: column;
    margin-left: 16px;
    padding-left: 40px;
    padding-bottom: 16px;
    padding-right: 12px;
    position: relative;
    border-left: 1px dashed #888;
    gap: 4px;
    font-size: 12px;
    max-width: 600px;
    width: 100%;
}

.hc-prospect-profile-history-row-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    background-color: #fff;
    border: 1px dashed #888;
    width: 28px;
    height: 28px;
    border-radius: 1000px;
    top: 0px;
    left: -15px;
    font-size: 12px;
}

.hc-prospect-profile-history-row-icon:before {
    content: "";
    display: inline-block;
    border-top: 1px dashed #888;
    position: absolute;
    top: 14px;
    left: 28px;
    width: 20px;
}

.hc-prospect-profile-history-row-footer {
    display: flex;
    flex-direction: row;
    gap: 12px;
    align-items: center;
}

.hc-prospect-profile-history-row-date {
    font-size: 12px;
}

.hc-prospect-profile-history-row-footer-separator {
    font-size: 3px;
    color: #888;
}

.hc-prospect-profile-history-row-user {
    font-size: 12px;
}
/*
@media (max-width: 767px) {
    .hc-prospect-profile-history-row {
        padding-left: 30px;
    }

    .hc-prospect-profile-history-row-icon:before {
        width: 10px;
    }
}
*/
</style>

<script>
export default {
    props: {
        item: {
            type: Object,
        },
        date: {
            type: String,
        },
        user: {
            type: Object,
        },
        icon: {
            type: String,
            default: "",
        },
        color: {
            type: String,
            default: "#000000",
        },
        bgcolor: {
            type: String,
            default: "#FFFFFF",
        },
    },

    computed: {
        date() {
            const date = dayjs(new Date(this.date)).format("DD MMM YYYY HH:mm");

            if (date == "Invalid date") {
                return this.date;
            }

            return date;
        },
    },
};
</script>
