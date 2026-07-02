<template>
    <slide
        :name="name"
        :title="$t('stat.user-connection-stat.title')"
        icon="fa fa-wifi"
        style="width: 260px"
        @open="fetchUserConnectionStats"
    >
        <tab-layout :count="2" :tab="tab" class="hc-flex-1">
            <template #1>
                <div
                    class="hc-flex-column"
                    style="height: 100%; position: relative; overflow: hidden"
                >
                    <item-list
                        class="hc-flex-1"
                        padding="12px"
                        style="height: 100%; overflow: auto"
                    >
                        <user-connection-stat-row
                            v-for="stat in stats"
                            :key="stat.month"
                            :stat="stat"
                            @click.prevent="fetchUsers(stat), (tab = 1)"
                        >
                        </user-connection-stat-row>
                    </item-list>
                    <loading :loading="fetchingConnectionsStat" />
                </div>
            </template>

            <template #2>
                <div
                    v-if="stat"
                    class="hc-flex-column"
                    style="height: 100%; position: relative"
                >
                    <item
                        @click="tab = 0"
                        style="border-bottom: 1px solid #eee"
                    >
                        <icon class="fa fa-caret-left" />
                        <div
                            class="hc-item-main-content hc-flex-column"
                            v-text="stat.month"
                        ></div>
                    </item>
                    <div
                        class="hc-flex-1 hc-flex-column"
                        style="position: relative; overflow: hidden"
                    >
                        <item-list
                            class="hc-flex-1"
                            padding="12px"
                            style="height: 100%; overflow: auto"
                        >
                            <user-row
                                v-for="user in users"
                                :key="user.id"
                                :user="user"
                                :project="project"
                            >
                            </user-row>
                        </item-list>
                        <loading :loading="fetchingUsers"></loading>
                    </div>
                </div>
            </template>
        </tab-layout>
    </slide>
</template>

<script>
import { mapGetters } from "vuex";

import UserConnectionStatService from "@/apis/project/user-connection-stat";

import UserConnectionStatRow from "./UserConnectionStatRow.vue";
import UserRow from "./UserRow.vue";

export default {
    components: {
        UserConnectionStatRow,
        UserRow,
    },

    data() {
        return {
            name: "user-connection-stat",
            tab: 0,
            stat: null,
            stats: [],
            users: [],

            fetchingConnectionsStat: false,
            fetchingUsers: false,
        };
    },

    methods: {
        /**
         * Fetch user connection stats
         */
        async fetchUserConnectionStats() {
            this.fetchingConnectionsStat = true;

            try {
                const { data } = await UserConnectionStatService.get(
                    this.project.slug
                );
                this.stats = data;
            } finally {
                this.fetchingConnectionsStat = false;
            }
        },

        /**
         * Fetch users
         */
        async fetchUsers(stat) {
            this.stat = stat;
            this.fetchingUsers = true;

            try {
                const { data } = await UserConnectionStatService.show(
                    this.project.slug,
                    this.stat.month
                );
                this.users = data;
            } finally {
                this.fetchingUsers = false;
            }
        },
    },

    watch: {
        project(newValue) {
            if (newValue && this.slideOpen(this.name)) {
                this.tab = 0;
                this.fetchUserConnectionStats();
            }
        },
    },

    computed: {
        ...mapGetters(["project", "slideOpen"]),
    },
};
</script>
