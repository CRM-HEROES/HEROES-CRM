<template>
    <modal :name="name" :title="$t('label.add.title')" @open="checkCategoryTab">
        <tab-layout :count="2" :tab="tab" class="hc-flex-1">
            <template #1>
                <div class="hc-flex-column" style="height: 100%">
                    <search v-model="categoryKeyword" />
                    <item-list class="hc-flex-1" padding="5px">
                        <category-row
                            v-for="c in filteredCategories"
                            :key="c.id"
                            :category="c"
                            @click="setCategory(c)"
                        />
                    </item-list>
                    <!--buttons>
                        <a @click.prevent="addCategory" v-text="$t('add')"></a>
                    </buttons-->
                </div>
            </template>
            <template #2>
                <form
                    class="hc-flex-column"
                    style="height: 100%"
                    v-if="category"
                    @submit.prevent="storeLabel"
                >
                    <item @click="setCategory(null)" class="bordered">
                        <icon class="fa fa-caret-left" />
                        <div
                            class="hc-item-main-content"
                            v-text="category.name"
                        ></div>
                    </item>
                    <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                        <v-field :label="$t('name')" required v-slot="p"
                            ><input
                                ref="labelName"
                                required
                                :placeholder="p.label + ' ...'"
                                v-model="label.name"
                        /></v-field>
                        <v-field :label="$t('description')" v-slot="p">
                            <textarea
                                v-model="label.description"
                                :placeholder="p.label + ' ...'"
                            ></textarea>
                        </v-field>
                        <v-field :label="$t('color')"
                            ><input type="color" v-model="label.color"
                        /></v-field>
                        <color-palette v-model="label.color" />
                        <v-field :label="$t('bgcolor')"
                            ><input type="color" v-model="label.bgcolor"
                        /></v-field>
                        <color-palette v-model="label.bgcolor" />
                    </item-list>
                    <buttons>
                        <button v-text="$t('add')"></button>
                    </buttons>
                    <loading :loading="addingLabel" />
                </form>
            </template>
        </tab-layout>
    </modal>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { ADD_LABEL } from "@/actions/project/label";
import { SET_CATEGORY } from "@/actions/project/category";
import { CLOSE_MODAL } from "@/actions/modal";

// Components
import CategoryRow from "./CategoryRow.vue";

export default {
    components: {
        CategoryRow,
    },

    data() {
        return {
            name: "label-add",
            tab: 0,
            label: this.newLabel(),
            addingLabel: false,
            categoryKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        newLabel() {
            return {
                name: "",
                description: "",
                color: "#FFFFFF",
                bgcolor: "#000000",
            };
        },

        /**
         *
         */
        async storeLabel() {
            this.addingLabel = true;

            try {
                await store.dispatch(ADD_LABEL, {
                    category: this.category.id,
                    params: this.label,
                });
            } finally {
                this.addingLabel = false;
                store.commit(CLOSE_MODAL);
                this.label = this.newLabel();
                this.$refs.labelName.focus();
            }
        },

        /**
         *
         * @param {*} category
         */
        setCategory(category) {
            store.commit(SET_CATEGORY, category);
        },

        async checkCategoryTab() {
            if (this.category) {
                if (this.modalOpen(this.name)) {
                    this.tab = 1;
                    await this.$nextTick();
                    setTimeout(() => {
                        this.$refs.labelName.focus();
                    }, 200);
                }
            } else {
                this.tab = 0;
            }
        },
    },

    watch: {
        category() {
            this.checkCategoryTab();
        },
    },

    computed: {
        ...mapGetters(["categories", "category", "modalOpen"]),

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
    },
};
</script>
