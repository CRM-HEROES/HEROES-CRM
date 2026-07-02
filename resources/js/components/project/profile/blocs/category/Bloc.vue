<template>
    <bloc icon="fa fa-tags" :name="$t('project.profile.blocs.categories')">
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus"
                @click.prevent.stop="addCategory"
            />
            <icon v-if="categories.length > 0" class="fa fa-caret-down" />
        </template>
        <template #body>
            <div
                style="padding: 10px 10px; float: left; width: 100%"
                v-if="categories.length > 0"
            >
                <category-row
                    v-for="(category, i) in categories"
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
import { OPEN_MODAL } from "@/actions/modal";

// Components
import Bloc from "@/components/project/profile/blocs/Bloc.vue";
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
            store.commit(OPEN_MODAL, "category-add");
        },
    },

    computed: {
        ...mapGetters(["project", "categories"]),
    },
};
</script>
