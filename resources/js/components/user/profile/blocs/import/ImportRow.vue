<template>
    <item @click.prevent.stop="edit">
        <icon class="fa fa-file-upload" :size="30" />
        <div class="hc-item-main-content" v-text="userImport.name"></div>
        <icon
            tag="a"
            class="fa fa-users hc-show-on-hover"
            :size="28"
            :icon-size="11"
            @click.prevent.stop="search"
        />
        <icon tag="a" class="fa fa-cog" :size="28" :icon-size="9" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";
import { SET_IMPORT } from "@/actions/project/import";

export default {
    props: {
        userImport: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_SLIDE, "import");
            store.commit(SET_IMPORT, this.userImport);
        },

        /**
         *
         */
        search() {
            this.$router.push({
                name: "user",
                params: {
                    project: this.project.slug,
                },
                query: {
                    filters: JSON.stringify({
                        withImports: [this.userImport.id],
                    }),
                },
            });
        },
    },

    computed: {
        ...mapGetters(["project"]),
    },
};
</script>
