<template>
    <item class="hc-user-action" tag="label">
        <icon :class="['fa', 'hc-user-action-icon', icon]" />
        <div class="hc-item-main-content hc-flex-column">
            <div class="hc-user-action-content" v-html="content"></div>
            <div class="hc-user-action-date" v-text="action.date"></div>
        </div>
    </item>
</template>

<style>
.hc-user-action {
    margin: 2px 0;
}

.hc-user-action-icon {
    width: 44px;
    height: 44px;
    font-size: 16px;
    border-radius: 50%;
    background-color: #eee;
    margin-right: 5px;
}

.hc-user-action-content {
    white-space: normal;
    font-size: 13px;
}

.hc-user-action-content > a {
    text-decoration: none;
    color: #7939b8;
}

.hc-user-action-date {
    opacity: 0.8;
}
</style>

<script>
import { mapGetters } from "vuex";

export default {
    props: {
        action: {
            type: Object,
        },
    },

    computed: {
        ...mapGetters(["user"]),

        content() {
            return "<b>" + this.user.name + "</b> " + this.action.content;
        },

        icon() {
            if (this.action.type == "create-prospect") {
                return "fa-plus icon-green";
            }

            if (this.action.type == "log") {
                return "fa-eye icon-blue";
            }

            if (this.action.type == "update-prospect") {
                return "fa-pencil icon-purple";
            }

            if (this.action.type == "show-prospect") {
                return "fa-user icon-blue";
            }

            if (this.action.type == "prospect-interaction") {
                return "fa-phone icon-orange";
            }

            if (this.action.type == "prospect-sms") {
                return "fa-comment icon-green";
            }

            if (this.action.type == "prospect-label") {
                return "fa-tag icon-brown";
            }

            if (this.action.type == "prospect-group") {
                return "fa-users icon-brown";
            }

            if (this.action.type == "prospect-user") {
                return "fa-user-plus icon-brown";
            }

            if (this.action.type == "create-import") {
                return "fa-upload icon-garnet";
            }

            if (this.action.type == "make-export") {
                return "fa-download icon-garnet";
            }

            if (this.action.type == "affect-event") {
                return "fa-calendar-check icon-purple";
            }

            if (this.action.type == "create-event") {
                return "fa-calendar-plus icon-purple";
            }

            if (this.action.type == "update-event") {
                return "fa-calendar icon-blue";
            }

            if (this.action.type == "add-order-product") {
                return "fa-gift icon-green";
            }

            if (this.action.type == "add-order-action") {
                return "fa-person-digging icon-green";
            }

            if (this.action.type == "add-order-step") {
                return "fa-step-forward icon-green";
            }

            if (this.action.type == "create-invoice") {
                return "fa-file-pdf icon-green";
            }

            if (this.action.type == "create-order") {
                return "fa-shopping-cart icon-purple";
            }

            if (this.action.type == "create-campaign") {
                return "fa-bullhorn icon-cyan";
            }

            if (this.action.type == "create-document") {
                return "fa-file-pdf-o icon-garnet";
            }

            if (this.action.type == "create-prospect-file") {
                return "fa-file icon-grey";
            }

            if (this.action.type == "add-prospect-message") {
                return "fa-envelope icon-green";
            }
        },
    },
};
</script>
