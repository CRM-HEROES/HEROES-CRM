<template>
    <div class="hc-pipeline-column-header">
        <div class="hc-pipeline-column-header-name" @click.stop="edit">
            <i class="fa fa-forward" :style="{ color: label.bgcolor }"></i>
            <span v-text="label.name"></span>
        </div>
        <router-link
            class="hc-pipeline-column-header-count"
            v-text="count + ' Prospect(s)'"
            :to="url"
        ></router-link>
    </div>
</template>

<style>
.hc-pipeline-column-header {
    display: flex;
    flex-direction: column;
    width: 100%;
    /* height: 34px; */
    position: sticky;
    top: 0;
    z-index: 2;
    font-size: 12px;
    padding: 10px 12px;
    background-color: #fff;
    border: 1px solid #ddd;
}

.hc-pipeline-column-header-name {
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
    cursor: pointer;
}

.hc-pipeline-column-header-name > i {
    font-size: 13px;
    margin-right: 8px;
}

.hc-pipeline-column-header-count {
    font-size: 11px;
    color: #7939b8;
    font-weight: 500;
    text-decoration: none;
}
</style>

<script>
import store from "@/store";
import { mapGetters } from "vuex";

import { OPEN_MODAL } from "@/actions/modal";
import { SET_LABEL } from "@/actions/project/label";

export default {
    props: {
        label: {
            type: Object,
        },
        count: {
            type: Number,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "label-update");
            store.commit(SET_LABEL, this.label);
        },
    },

    computed: {
        ...mapGetters(["project"]),

        url() {
            const routeData = this.$router.resolve({
                name: "prospect",
                params: {
                    project: this.project.slug,
                },
                query: {
                    filters: JSON.stringify({
                        ["withCategory_" + this.label.category_id]: [
                            this.label.id,
                        ],
                    }),
                },
            });

            return routeData.href;
        },
    },
};
</script>
