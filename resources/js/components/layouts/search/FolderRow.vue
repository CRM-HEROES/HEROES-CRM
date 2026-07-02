<template>
    <item @click="search">
        <icon class="fa fa-folder" :size="28" :style="style" />
        <div class="hc-item-main-content" v-text="folder.name"></div>
        <icon
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            :size="28"
            @click.prevent.stop="edit"
        />
        <icon
            tag="a"
            class="fa fa-file"
            color="#007afd"
            :size="28"
            @click.prevent.stop="fileProspect"
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
import { OPEN_SLIDE } from "@/actions/slide";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_FOLDER } from "@/actions/project/folder";

import { SET_PROSPECT } from "@/actions/project/prospect";
import { SET_PROSPECT_FILE_FOLDER } from "@/actions/project/prospect/file";
import {
    INIT_PROSPECT_PARAMS,
    SET_PROSPECT_PARAMS,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";

export default {
    props: {
        folder: {
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

        fileProspect() {
            store.commit(SET_PROSPECT, null);
            store.commit(SET_PROSPECT_FILE_FOLDER, this.folder);
            store.commit(OPEN_SLIDE, "prospect-manage-files");
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
            store.commit(OPEN_MODAL, "folder-update");
            store.commit(SET_FOLDER, this.folder);
        },
    },

    computed: {
        ...mapGetters(["project"]),

        /**
         *
         */
        style() {
            return {
                color: this.folder.bgcolor,
            };
        },

        /**
         *
         */
        filter() {
            return {
                withFiles: [this.folder.id],
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
