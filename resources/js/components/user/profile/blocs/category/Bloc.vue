<template>
    <bloc
        icon="fa fa-tags icon-garnet"
        :name="$t('user.profile.blocs.categories')"
    >
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus icon-blue"
                @click.prevent.stop="addCategory"
            />
        </template>
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <category-row
                    v-for="(category, i) in userCategories"
                    :key="category.id"
                    :category="category"
                />
            </div>
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";

// Components
import Bloc from "@/components/user/profile/blocs/Bloc.vue";
import CategoryRow from "./CategoryRow.vue";

export default {
    components: {
        Bloc,
        CategoryRow,
    },

    methods: {
        /**
         * Add category
         * See: @/components/category/add/Modal.vue
         */
        addCategory() {
            store.commit(OPEN_SLIDE, "user-manage-categories");
        },
    },

    computed: {
        ...mapGetters(["project", "userCategories"]),
    },
};
</script>
