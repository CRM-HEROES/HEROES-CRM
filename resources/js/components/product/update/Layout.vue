<template>
    <tab-layout :count="3" :tab="tab" class="hc-flex-1" v-if="productToUpdate">
        <template #1>
            <form
                class="hc-flex-column"
                style="height: 100%"
                @submit.prevent="update"
            >
                <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                    <v-field :label="$t('name')" required v-slot="{ label }"
                        ><input
                            required
                            :placeholder="label + ' ...'"
                            v-model="productToUpdate.name"
                    /></v-field>
                    <v-field :label="$t('description')" v-slot="p">
                        <textarea
                            v-model="productToUpdate.description"
                            :placeholder="p.label + ' ...'"
                        ></textarea>
                    </v-field>
                    <v-field label="Prix" required v-slot="{ label }"
                        ><input
                            required
                            :placeholder="label + ' ...'"
                            v-model="price"
                    /></v-field>
                    <v-field label="Devise" required v-slot="{ label }"
                        ><input
                            required
                            :placeholder="label + ' ...'"
                            v-model="productToUpdate.currency"
                    /></v-field>
                    <v-field label="Taxe en %" required v-slot="{ label }"
                        ><input
                            required
                            :placeholder="label + ' ...'"
                            v-model="tax"
                    /></v-field>
                    <item tag="label" style="padding: 0 5px">
                        <icon class="fa fa-euro" :style="style" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('product.included_tax')"
                        ></div>
                        <checkbox v-model="productToUpdate.including_tax" />
                    </item>
                    <item style="padding: 0 5px" @click="tab = 1">
                        <icon class="fa fa-image" :style="style" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('product.manage_images')"
                        ></div>
                        <icon tag="a" class="fa fa-caret-right" />
                    </item>
                    <item tag="label" style="padding-right: 5px">
                        <icon class="fa fa-eye-slash" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('product.archive')"
                            v-tooltip="$t('product.archive_tooltip')"
                        ></div>
                        <checkbox v-model="productToUpdate.archive" />
                    </item>
                </item-list>
                <item @click="tab = 2">
                    <icon class="fa fa-users" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('label.affected_users')"
                    ></div>
                    <icon class="fa fa-caret-right" />
                </item>
                <buttons>
                    <button
                        v-if="can('all.project.order.product.delete')"
                        @click.prevent="remove"
                        class="hc-button-danger"
                        v-text="$t('delete')"
                    ></button>
                    <button v-text="$t('update')"></button>
                </buttons>
                <loading :loading="updatingProduct || removingProduct" />
            </form>
        </template>

        <template #2>
            <div class="hc-flex-column" style="height: 100%">
                <item style="padding: 0 5px" @click="tab = 0">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('product.add_images')"
                    ></div>
                </item>
                <draggable
                    tag="image-row"
                    :list="productToUpdate.images"
                    style="
                        overflow: auto;
                        padding: 8px;
                        width: 100%;
                        height: 100%;
                        background-color: rgb(245, 245, 245);
                        border-radius: 15px;
                        display: flex;
                        flex-direction: row;
                        flex-wrap: wrap;
                        align-items: start;
                        align-content: center;
                    "
                    item-key="id"
                    @end="updateProductImageOrder"
                    @dragenter.prevent
                    @dragover.prevent
                    @dragleave.prevent
                    @drop.prevent="dropImages"
                >
                    <template #item="{ element }">
                        <image-row :image="element" :product="product" />
                    </template>
                    <template #footer>
                        <uploading-image-row
                            v-for="c in uploadingImages"
                            :key="c.name"
                            :product="product"
                            :image="c"
                            @image-uploaded="imageUploaded"
                        />
                    </template>
                </draggable>

                <buttons>
                    <label
                        >{{ $t("product.update.choose_images") }}
                        <input
                            type="file"
                            accept="image/*"
                            @change="addImages"
                            multiple
                    /></label>
                </buttons>
            </div>
        </template>

        <template #3>
            <div class="hc-flex-column" style="height: 100%">
                <item @click="tab = 0">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('label.affected_users')"
                    ></div>
                </item>
                <search v-model="userKeyword" />
                <item-list
                    class="hc-flex-1"
                    padding="5px"
                    style="max-height: 400px"
                >
                    <!-- All -->
                    <item tag="label">
                        <icon class="fa fa-check-square" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('all')"
                        ></div>
                        <checkbox v-model="all" />
                    </item>
                    <user-row
                        v-for="user in filteredUsers"
                        :key="user.id"
                        :user="user"
                        :product="product"
                        v-model="productUsers"
                    />
                </item-list>
                <loading :loading="fetchingProductUsers" />
            </div>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import UserProductService from "@/apis/project/user/product";

// Actions
import {
    UPDATE_PRODUCT,
    REMOVE_PRODUCT,
    UPDATE_PRODUCT_IMAGE,
} from "@/actions/project/product";
import { CLOSE_MODAL } from "@/actions/modal";

// Components
import UploadingImageRow from "./UploadingImageRow.vue";
import ImageRow from "./ImageRow.vue";
import UserRow from "./UserRow.vue";

export default {
    components: {
        UploadingImageRow,
        ImageRow,
        UserRow,
    },

    data() {
        return {
            tab: 0,
            updatingProduct: false,
            removingProduct: false,
            fetchingProduct: false,
            uploadingImages: [],
            productToUpdate: this.product,
            userKeyword: "",
            productUsers: [],
        };
    },

    created() {
        this.productToUpdate = this.product;
        this.fetchProductUsers();
    },

    methods: {
        /**
         *
         */
        async update() {
            this.updatingProduct = true;

            try {
                await store.dispatch(UPDATE_PRODUCT, this.productToUpdate);

                if (this.productToUpdate.archive) {
                    store.commit(REMOVE_PRODUCT, this.productToUpdate.id);
                }
            } finally {
                this.updatingProduct = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingProduct = true;

                try {
                    await store.dispatch(
                        REMOVE_PRODUCT,
                        this.productToUpdate.id
                    );
                } finally {
                    this.removingProduct = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },

        /**
         *
         */
        async addImages(e) {
            this.uploadingImages = [...this.uploadingImages, ...e.target.files];
        },

        /**
         *
         */
        async dropImages(e) {
            this.uploadingImages = [
                ...this.uploadingImages,
                ...e.dataTransfer.files,
            ];
        },

        /**
         *
         * @param {*} uploading
         * @param {*} uploaded
         */
        imageUploaded(uploading) {
            this.uploadingImages = this.uploadingImages.filter(
                (f) => f != uploading
            );
        },

        /**
         *
         * @param {*} e
         */
        updateProductImageOrder(e) {
            const image = this.product.images[e.newDraggableIndex];

            store.dispatch(UPDATE_PRODUCT_IMAGE, {
                id: image.id,
                product_id: image.product_id,
                order: e.newDraggableIndex,
            });
        },

        /**
         *
         */
        async fetchProductUsers() {
            if (!this.productToUpdate) {
                return;
            }

            this.fetchingProductUsers = true;

            try {
                const { data } = await UserProductService.users(
                    this.project.slug,
                    this.productToUpdate.id
                );
                this.productUsers = data.map((user) => user.id);
            } finally {
                this.fetchingProductUsers = false;
            }
        },
    },

    watch: {
        async product(newValue) {
            if (newValue) {
                this.productToUpdate = { ...newValue, archive: false };
                this.fetchProductUsers();

                /*this.fetchingProduct = true;
                this.productToUpdate = await store.dispatch(
                    SHOW_PRODUCT,
                    newValue.id
                );
                this.fetchingProduct = false;*/
            }
        },
    },

    computed: {
        ...mapGetters(["project", "product", "users", "can"]),

        /**
         *
         */
        all: {
            get: function () {
                return this.productUsers.length == this.users.length;
            },
            set: async function (value) {
                if (value) {
                    const users = this.users.map((f) => f.id);

                    UserProductService.bulkUpdate(this.project.slug, users, [
                        this.product.id,
                    ]);
                    this.productUsers = users;
                } else {
                    UserProductService.bulkDestroy(
                        this.project.slug,
                        this.productUsers,
                        [this.product.id]
                    );
                    this.productUsers = [];
                }
            },
        },

        /**
         *
         */
        price: {
            get() {
                return this.productToUpdate.price;
            },
            set(value) {
                this.productToUpdate.price = parseFloat(
                    value.replace(",", ".")
                );
            },
        },

        /**
         *
         */
        tax: {
            get() {
                return this.productToUpdate.tax * 100;
            },
            set(value) {
                this.productToUpdate.tax =
                    parseFloat(value.replace(",", ".")) / 100;
            },
        },

        /**
         *
         */
        filteredUsers() {
            const keyword = removeStringAccent(this.userKeyword);

            return this.users.filter(
                (user) => removeStringAccent(user.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
