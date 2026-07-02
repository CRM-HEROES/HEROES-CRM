<template>
    <bloc
        icon="fa fa-tags icon-garnet"
        :name="$t('user.profile.blocs.categories')"
    >
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

// Components
import Bloc from "@/components/user/profile/blocs/Bloc.vue";
import CategoryRow from "./CategoryRow.vue";

export default {
    components: {
        Bloc,
        CategoryRow,
    },

    computed: {
        ...mapGetters(["globalUser"]),

        userCategories() {
            return Array.isArray(this.globalUser.categories)
                ? this.globalUser.categories
                : [];
        },
    },
};
</script>
