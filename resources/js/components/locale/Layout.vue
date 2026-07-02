<template>
    <item-list style="height: 100%; overflow: auto" padding="12px">
        <item
            v-for="locale in locales"
            tag="a"
            @click.prevent="setLocale(locale.locale)"
        >
            <icon>
                <div v-html="locale.flag"></div>
            </icon>
            <div class="hc-item-main-content" v-text="locale.name"></div>
        </item>
    </item-list>
</template>

<style></style>

<script>
import store from "@/store";
import locales from "@/utils/locale";

// Actions
import { CLOSE_SLIDE } from "@/actions/slide";

export default {
    data() {
        return {
            locales: locales,
        };
    },

    methods: {
        setLocale(locale) {
            this.$i18n.locale = locale;
            store.commit("setLocale", locale);
            store.commit(CLOSE_SLIDE);
        },
    },
};
</script>
