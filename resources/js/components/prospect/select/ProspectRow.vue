<template>
    <item>
        <icon class="fa fa-user" />
        <div class="hc-item-main-content" v-text="fullName"></div>
        <!--icon class="fa fa-cog" @click.stop.prevent="edit" /-->
    </item>
</template>

<script>
/*
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_PROSPECT } from "@/actions/project/prospect";
*/
export default {
    props: {
        prospect: {
            type: Object,
        },
    },
    /*
    methods: {
        edit(e) {
            store.commit(OPEN_MODAL, "prospect-update");
            store.commit(SET_PROSPECT, this.prospect);
        },
    },*/

    computed: {
        /**
         *
         */
        fullName() {
            return [this.prospect.first_name, this.prospect.last_name]
                .filter((n) => n)
                .join(" ");
        },
    },
};
</script>
