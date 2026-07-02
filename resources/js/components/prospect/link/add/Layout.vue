<template>
    <form
        class="hc-flex-column"
        style="height: 100%"
        @submit.prevent="storeLink"
    >
        <item-list gap="5px">
            <v-field :label="$t('name')" required v-slot="{ label }"
                ><input
                    :placeholder="label + ' ...'"
                    v-model="link.name"
                    required
            /></v-field>
            <v-field :label="$t('url')" required v-slot="{ label }"
                ><input
                    :placeholder="label + ' ...'"
                    v-model="link.link"
                    required
            /></v-field>
        </item-list>
        <buttons>
            <button v-text="$t('add')"></button>
        </buttons>
        <loading :loading="addingLink" />
    </form>
</template>

<script>
import store from "@/store";

// Actions
import { ADD_PROSPECT_LINK } from "@/actions/project/prospect/link";
import { CLOSE_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            link: this.newLink(),
            addingLink: false,
        };
    },

    methods: {
        /**
         *
         */
        newLink() {
            return {
                name: "",
                link: "",
            };
        },

        /**
         *
         */
        async storeLink() {
            this.addingLink = true;

            try {
                await store.dispatch(ADD_PROSPECT_LINK, this.link);
            } finally {
                this.addingLink = false;
                this.link = this.newLink();
                store.commit(CLOSE_MODAL);
            }
        },
    },
};
</script>
