<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="categoryKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label">
                <icon class="fa fa-check-square" />
                <div class="hc-item-main-content" v-text="$t('all')"></div>
                <checkbox v-model="all" />
            </item>
            <category-row
                v-for="category in filteredCategories"
                :key="category.id"
                :category="category"
            />
        </item-list>
        <buttons>
            <button @click.prevent="attach" :disabled="disabled">
                Assigner
            </button>
            <button
                @click.prevent="detach"
                :disabled="disabled"
                class="hc-button-danger"
            >
                Retirer
            </button>
            <a
                v-if="can('all.project.category.add')"
                @click.prevent="addCategory"
                v-text="$t('add')"
            ></a>
        </buttons>
        <loading :loading="bulking" />
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    BULK_ADD_USER_CATEGORY,
    BULK_REMOVE_USER_CATEGORY,
} from "@/actions/project/user/category";
import {
    FETCH_USERS,
    UPDATE_SELECTED_USERS,
    UPDATE_USER_BULK_CATEGORIES,
} from "@/actions/project/user";
import { OPEN_MODAL } from "@/actions/modal";
import { CLOSE_SLIDE } from "@/actions/slide";

// Components
import CategoryRow from "./CategoryRow.vue";

export default {
    components: {
        CategoryRow,
    },

    data() {
        return {
            bulking: false,
            categoryKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        addCategory() {
            store.commit(OPEN_MODAL, "category-add");
        },

        /**
         *
         */
        async attach() {
            this.bulking = true;
            const usersSelected = this.usersSelected;
            store.commit(UPDATE_SELECTED_USERS, []);

            try {
                await store.dispatch(BULK_ADD_USER_CATEGORY, {
                    users: usersSelected,
                    categories: this.userBulkCategories,
                });
                store.dispatch(FETCH_USERS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_USER_BULK_CATEGORIES, []);
                store.commit(CLOSE_SLIDE, "user-bulk-manage-categories");
            }
        },

        /**
         *
         */
        async detach() {
            this.bulking = true;
            const usersSelected = this.usersSelected;
            store.commit(UPDATE_SELECTED_USERS, []);

            try {
                await store.dispatch(BULK_REMOVE_USER_CATEGORY, {
                    users: usersSelected,
                    categories: this.userBulkCategories,
                });
                store.dispatch(FETCH_USERS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_USER_BULK_CATEGORIES, []);
                store.commit(CLOSE_SLIDE, "user-bulk-manage-categories");
            }
        },
    },

    computed: {
        ...mapGetters([
            "categories",
            "usersSelected",
            "userBulkCategories",
            "can",
        ]),

        /**
         *
         */
        disabled() {
            return this.userBulkCategories.length == 0;
        },

        /**
         *
         */
        filteredCategories() {
            const keyword = removeStringAccent(this.categoryKeyword);

            return this.categories.filter(
                (category) =>
                    removeStringAccent(category.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        all: {
            get: function () {
                for (let i in this.filteredCategories) {
                    if (
                        !this.userBulkCategories.find(
                            (category) =>
                                category == this.filteredCategories[i].id
                        )
                    ) {
                        return false;
                    }
                }

                return true;
            },
            set: async function (value) {
                if (value) {
                    store.commit(
                        UPDATE_USER_BULK_CATEGORIES,
                        this.filteredCategories.map((category) => category.id)
                    );
                } else {
                    store.commit(UPDATE_USER_BULK_CATEGORIES, []);
                }
            },
        },
    },
};
</script>
