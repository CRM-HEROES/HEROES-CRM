<template>
    <slide
        :name="name"
        :title="$t('campaign.action.prospect.message.title')"
        icon="fa fa-envelope"
        style="width: 300px"
    >
        <tab-layout :count="2" :tab="tab" class="hc-flex-1">
            <template #1>
                <div class="hc-flex-column" style="height: 100%">
                    <item-list padding="12px" style="height: auto">
                        <item
                            class="hc-campaign-action-message-item"
                            @click="(frameTab = 0), (tab = 1)"
                        >
                            <icon class="fa fa-envelope" :size="36" />
                            <div class="hc-item-main-content hc-flex-column">
                                <span
                                    class="hc-campaign-action-message-item-title"
                                    v-text="
                                        $t(
                                            'campaign.action.prospect.message.thread.title'
                                        )
                                    "
                                ></span>
                                <span
                                    class="hc-campaign-action-message-item-value"
                                    v-text="
                                        thread
                                            ? thread.name
                                            : $t(
                                                  'campaign.action.prospect.message.thread.placeholder'
                                              )
                                    "
                                ></span>
                            </div>
                            <icon class="fa fa-caret-right" />
                        </item>
                        <item
                            class="hc-campaign-action-message-item"
                            @click="(frameTab = 1), (tab = 1)"
                        >
                            <icon class="fa fa-user-plus" :size="36" />
                            <div class="hc-item-main-content hc-flex-column">
                                <span
                                    class="hc-campaign-action-message-item-title"
                                    v-text="
                                        $t(
                                            'campaign.action.prospect.message.user.title'
                                        )
                                    "
                                ></span>
                                <span
                                    class="hc-campaign-action-message-item-value"
                                    v-text="
                                        user
                                            ? user.name
                                            : $t(
                                                  'campaign.action.prospect.message.user.placeholder'
                                              )
                                    "
                                ></span>
                            </div>
                            <icon class="fa fa-caret-right" />
                        </item>
                        <item @click="(frameTab = 2), (tab = 1)">
                            <icon class="fa fa-file-text" :size="36" />
                            <div
                                class="hc-item-main-content hc-flex-column"
                                v-text="
                                    $t(
                                        'campaign.action.prospect.message.template.title'
                                    )
                                "
                            ></div>
                            <icon class="fa fa-caret-right" />
                        </item>
                        <text-editor
                            v-model.lazy="message.value.body"
                            :placeholder="
                                $t(
                                    'campaign.action.prospect.message.enter_message'
                                )
                            "
                            height="200px"
                        />
                    </item-list>
                    <buttons>
                        <campaign-template
                            tag="button"
                            :field="message"
                            @dragging="dragging"
                            @dragged="dragged"
                            v-html="
                                '<i class=&quot;fa fa-arrows&quot;></i>&nbsp;&nbsp;' +
                                $t('add')
                            "
                            :disabled="
                                !message.value.body || !message.value.thread
                            "
                        ></campaign-template>
                    </buttons>
                </div>
            </template>

            <!-- List of threads -->
            <template #2>
                <frame-layout :count="3" :tab="frameTab" class="hc-flex-1">
                    <template #1>
                        <div class="hc-flex-column" style="height: 100%">
                            <item @click="tab = 0" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t(
                                            'campaign.action.prospect.message.thread.title'
                                        )
                                    "
                                ></div>
                            </item>
                            <search v-model="threadKeyword" />
                            <item-list class="hc-flex-1" padding="5px">
                                <thread-row
                                    v-for="c in filteredThreads"
                                    :key="c.id"
                                    :thread="c"
                                    @click="
                                        (message.value.thread = c.id), (tab = 0)
                                    "
                                />
                            </item-list>
                            <buttons>
                                <a
                                    @click.prevent="addThread"
                                    v-text="$t('add')"
                                ></a>
                            </buttons>
                        </div>
                    </template>
                    <template #2>
                        <div class="hc-flex-column" style="height: 100%">
                            <item @click="tab = 0" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t(
                                            'campaign.action.prospect.message.user.title'
                                        )
                                    "
                                ></div>
                            </item>
                            <search v-model="userKeyword" />
                            <item-list class="hc-flex-1" padding="5px">
                                <user-row
                                    v-for="c in filteredUsers"
                                    :key="c.id"
                                    :user="c"
                                    @click="
                                        (message.value.user = c.id), (tab = 0)
                                    "
                                />
                            </item-list>
                        </div>
                    </template>
                    <template #3>
                        <div class="hc-flex-column" style="height: 100%">
                            <item @click="tab = 1" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="$t('prospect.message.templates')"
                                ></div>
                            </item>
                            <search v-model="messageTemplateKeyword" />
                            <item-list class="hc-flex-1" padding="5px">
                                <template-row
                                    v-for="c in filteredMessageTemplates"
                                    :key="c.id"
                                    :template="c"
                                    @click="setMessageTemplate(c)"
                                />
                            </item-list>
                            <buttons
                                v-if="can('all.project.message-template.add')"
                            >
                                <a
                                    @click.prevent="addMessageTemplate"
                                    v-text="$t('add')"
                                ></a>
                            </buttons>
                        </div>
                    </template>
                </frame-layout>
            </template>
        </tab-layout>
    </slide>
</template>

<style>
.hc-campaign-action-message-item {
    padding: 3px 0 !important;
    align-items: start;
}

.hc-campaign-action-message-item-title {
    color: #000000;
}

.hc-campaign-action-message-item-value {
    color: #000000;
    font-size: 11px;
    opacity: 0.7;
}
</style>

<script>
import store from "@/store";
import { mapGetters } from "vuex";

// Actions
import { CLOSE_SLIDE } from "@/actions/slide";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import ThreadRow from "./ThreadRow.vue";
import UserRow from "./UserRow.vue";
import TemplateRow from "./TemplateRow.vue";
import CampaignTemplate from "@/components/campaign/components/Template.vue";

export default {
    components: {
        ThreadRow,
        UserRow,
        TemplateRow,
        CampaignTemplate,
    },

    data() {
        return {
            name: "campaign-action-prospect-message",
            tab: 0,
            frameTab: 0,
            threadKeyword: "",
            userKeyword: "",
            messageTemplateKeyword: "",
            message: {
                action: "prospect-message",
                name: this.$t("campaign.action.prospect.message.name"),
                value: {
                    body: "",
                    thread: null,
                    user: null,
                },
                category: "action",
                style: {},
            },
        };
    },

    methods: {
        /**
         *
         */
        addThread() {
            store.commit(OPEN_MODAL, "thread-add");
        },

        /**
         *
         */
        setMessageTemplate(template) {
            this.message.value.body = template.body;
            this.tab = 0;
        },

        /**
         *
         */
        addMessageTemplate() {
            store.commit(OPEN_MODAL, "message-template-add");
        },

        dragging(field) {
            this.$emit("dragging", field);
        },

        dragged(x, y) {
            this.$emit("dragged", x, y);
            store.commit(CLOSE_SLIDE);
        },
    },

    computed: {
        ...mapGetters(["threads", "users", "messageTemplates", "can"]),

        thread() {
            return this.threads.find(
                (thread) => thread.id === this.message.value.thread
            );
        },

        user() {
            return this.users.find(
                (user) => user.id === this.message.value.user
            );
        },

        actionName() {
            if (!this.thread) {
                return;
            }

            return this.$t("campaign.action.prospect.message.name", {
                thread: this.thread.name,
                content:
                    this.message.value.body
                        .replace(/<[^>]*>/g, "")
                        .substring(0, 50) + " ...",
            });
        },

        body() {
            return this.message.value.body;
        },

        /**
         *
         */
        filteredThreads() {
            const keyword = removeStringAccent(this.threadKeyword);

            return this.threads.filter(
                (thread) =>
                    removeStringAccent(thread.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredUsers() {
            const keyword = removeStringAccent(this.userKeyword);

            return this.users.filter(
                (user) => removeStringAccent(user.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredMessageTemplates() {
            const keyword = removeStringAccent(this.messageTemplateKeyword);

            return this.messageTemplates.filter(
                (messageTemplate) =>
                    removeStringAccent(messageTemplate.name).indexOf(keyword) >=
                    0
            );
        },
    },

    watch: {
        thread() {
            if (this.thread) {
                this.message.name = this.actionName;
            }
        },

        body() {
            if (this.thread) {
                this.message.name = this.actionName;
            }
        },
    },
};
</script>
