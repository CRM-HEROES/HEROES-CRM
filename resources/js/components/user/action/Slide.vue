<template>
    <slide
        name="user-manage-actions"
        :title="$t('user.actions.title')"
        icon="fa fa-person-digging"
        style="width: 360px"
        @open="(actions = []), (page = 1), fetchActions()"
        @first-open="scrollListener"
    >
        <div
            id="hc-user-actions"
            style="height: 100%; overflow: auto; position: relative"
        >
            <item-list
                id="hc-user-actions-content"
                padding="12px"
                style="height: auto"
            >
                <template v-for="(action, i) in actions" :key="action.id">
                    <div
                        v-if="
                            i == 0 ||
                            action.action_at.substring(0, 10) !=
                                actions[i - 1].action_at.substring(0, 10)
                        "
                        :key="action.action_at.substring(0, 10)"
                        class="hc-action-date"
                        v-text="actionDate(action)"
                    ></div>

                    <action-row :action="action" />
                </template>
            </item-list>
            <loading :loading="fetchingActions && page == 1" />
        </div>
    </slide>
</template>

<style>
.hc-action-date {
    width: 100%;
    text-align: right;
    height: 35px;
    line-height: 35px;
    font-weight: 500;
    opacity: 0.7;
    border-bottom: 1px solid #0002;
    margin-bottom: 5px;
}
</style>

<script>
import store from "@/store";

import { FETCH_USER_ACTIONS } from "@/actions/project/user/action";

// Components
import ActionRow from "./ActionRow.vue";

export default {
    components: {
        ActionRow,
    },

    data() {
        return {
            fetchingActions: false,
            actions: [],
            page: 1,
            count: 30,
        };
    },

    methods: {
        async fetchActions() {
            if (this.page == 0) {
                return;
            }

            this.fetchingActions = true;

            try {
                const data = await store.dispatch(FETCH_USER_ACTIONS, {
                    params: {
                        page: this.page,
                        count: this.count,
                    },
                });

                this.actions = [...this.actions, ...data];

                if (data.length == this.count) {
                    this.page++;
                } else {
                    this.page = 0;
                }
            } finally {
                this.fetchingActions = false;
            }
        },

        actionDate(action) {
            return dayjs(action.action_at).format("DD/MM/YYYY");
        },

        scrollListener() {
            let userActions = document.getElementById("hc-user-actions");
            let userActionsContent = document.getElementById(
                "hc-user-actions-content"
            );

            if (userActions) {
                userActions.addEventListener("scroll", (event) => {
                    if (
                        userActions.scrollTop >=
                            userActionsContent.getBoundingClientRect().height -
                                userActions.getBoundingClientRect().height -
                                100 &&
                        !this.fetchingActions
                    ) {
                        this.fetchActions();
                    }
                });
            }
        },
    },
};
</script>
