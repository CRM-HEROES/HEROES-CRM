<template>
    <item @click="search">
        <icon class="fa fa-comments" :size="28" :style="style" />
        <div class="hc-item-main-content" v-text="thread.name"></div>
        <icon
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            :size="28"
            @click.prevent.stop="edit"
        />
        <icon
            tag="a"
            class="fa fa-paper-plane"
            color="#007afd"
            :size="28"
            @click.prevent.stop="messageProspect"
        />
        <icon
            tag="a"
            class="fa fa-globe-europe"
            color="#007afd"
            :size="28"
            @click.prevent.stop="map"
        />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { OPEN_SLIDE } from "@/actions/slide";
import { SET_THREAD } from "@/actions/project/thread";

import { SET_PROSPECT } from "@/actions/project/prospect";
import { SET_PROSPECT_MESSAGE_THREAD } from "@/actions/project/prospect/message";
import {
    INIT_PROSPECT_PARAMS,
    SET_PROSPECT_PARAMS,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";

export default {
    props: {
        thread: {
            type: Object,
        },
    },

    methods: {
        /**
         *
         */
        search() {
            if (this.$route.name == "prospect") {
                store.commit(INIT_PROSPECT_PARAMS);

                store.commit(SET_PROSPECT_PARAMS, this.filter);
                store.dispatch(FETCH_PROSPECTS);
            } else {
                this.$router.push({
                    name: "prospect",
                    params: {
                        project: this.project.slug,
                    },
                    query: this.query,
                });
            }
        },

        messageProspect() {
            store.commit(SET_PROSPECT, null);
            store.commit(SET_PROSPECT_MESSAGE_THREAD, this.thread);
            store.commit(OPEN_SLIDE, "prospect-manage-messages");
        },

        /**
         *
         */
        map() {
            if (this.$route.name == "map") {
                store.commit(INIT_PROSPECT_PARAMS);

                store.commit(SET_PROSPECT_PARAMS, this.filter);
                store.dispatch(FETCH_PROSPECTS);
            } else {
                this.$router.push({
                    name: "map",
                    params: {
                        project: this.project.slug,
                    },
                    query: this.query,
                });
            }
        },

        edit(e) {
            store.commit(OPEN_MODAL, "thread-update");
            store.commit(SET_THREAD, this.thread);
        },
    },

    computed: {
        ...mapGetters(["project"]),

        /**
         *
         */
        style() {
            return {
                color: this.thread.bgcolor,
            };
        },

        /**
         *
         */
        filter() {
            return {
                withMessages: [this.thread.id],
            };
        },

        /**
         *
         */
        query() {
            return {
                filters: JSON.stringify(this.filter),
            };
        },
    },
};
</script>
