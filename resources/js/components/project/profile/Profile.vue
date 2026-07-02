<template>
    <div id="hc-project-profile">
        <div id="hc-project-profile-header">
            <div id="hc-project-profile-title" v-text="project.name"></div>
            <icon
                class="fa fa-envelope"
                color="#da3c58"
                v-tooltip="$t('project.profile.menus.thread')"
                :icon-size="16"
                @click.prevent="addThread"
            />
            <icon
                class="fa fa-folder"
                color="#1e6ee5"
                v-tooltip="$t('project.profile.menus.folder')"
                :icon-size="16"
                @click.prevent="addFolder"
            />
            <icon
                class="fa fa-tags"
                color="#489f1f"
                v-tooltip="$t('project.profile.menus.category')"
                :icon-size="16"
                @click.prevent="addCategory"
            />
            <icon
                class="fa fa-columns"
                color="#9b6138"
                v-tooltip="$t('project.profile.menus.field')"
                :icon-size="16"
                @click.prevent="addField"
            />
            <icon
                class="fa fa-users"
                color="#1e6ee5"
                v-tooltip="$t('project.profile.menus.group')"
                :icon-size="16"
                @click.prevent="addGroup"
            />
            <icon
                class="fa fa-user-tie"
                color="#e5961e"
                v-tooltip="$t('project.profile.menus.role')"
                :icon-size="16"
                @click.prevent="addRole"
            />
            <icon
                class="fa fa-gift"
                color="#9b6138"
                v-tooltip="$t('project.profile.menus.product')"
                :icon-size="16"
                @click.prevent="addProduct"
            />
        </div>
        <div id="hc-project-profile-body">
            <div id="hc-project-profile-body-columns">
                <div>
                    <label id="hc-project-profile-logo">
                        <img
                            :src="
                                project.thumbnail
                                    ? project.thumbnail
                                    : '/images/logo.png'
                            "
                            :alt="project.name"
                        />
                        <input
                            type="file"
                            id="hc-project-profile-logo-input"
                            style="width: 0px; height: 0px"
                            @change="setLogo"
                        />
                        <loading :loading="updatingLogo" />
                        <div
                            id="hc-project-profile-logo-change"
                            v-text="$t('project.profile.logo.change')"
                        ></div>
                    </label>
                    <user-bloc />
                    <role-bloc />
                    <group-bloc />
                    <menu-bloc />
                    <api-bloc />
                    <import-bloc />
                </div>
                <div>
                    <info-bloc
                        :bloc="{
                            name: $t('project.profile.blocs.profile'),
                            items: ['name', 'email', 'phone_number'],
                        }"
                    />
                    <info-bloc
                        :bloc="{
                            name: $t('project.profile.blocs.contacts'),
                            items: [
                                'street',
                                'street_bis',
                                'postal_code',
                                'city',
                                'county',
                                'state',
                                'country',
                            ],
                        }"
                    />
                    <info-bloc
                        :bloc="{
                            name: 'Autres',
                            items: ['naf', 'siret', 'capital', 'num_tva_intra'],
                        }"
                    />
                    <document-bloc />
                </div>
                <div>
                    <category-bloc />
                    <field-bloc />
                </div>
                <div>
                    <folder-bloc />
                    <thread-bloc />
                    <calendar-bloc />
                    <product-bloc />
                    <order-action-bloc />
                    <order-step-bloc />
                    <order-status-bloc />
                </div>
            </div>
        </div>
        <project-profile-setting-slide />
    </div>
</template>

<style scoped>
#hc-project-profile {
    width: 100%;
    display: flex;
    flex-direction: column;
    height: 100%;
    background-color: #f4f4f4;
}

#hc-project-profile-header {
    display: flex;
    flex-direction: row;
    align-items: center;
}

#hc-project-profile-title {
    flex: 1;
    padding: 15px 0 15px 15px;
    font-weight: bold;
    font-size: 15px;
}

#hc-project-profile-body {
    flex: 1;
    overflow: auto;
    display: flex;
    flex-direction: row;
    justify-content: center;
    gap: 15px;
    padding: 0 10px;
}

#hc-project-profile-body-columns {
    width: 1200px;
    max-width: 100%;
    display: flex;
    flex-direction: row;
    padding: 0 0;
    flex-wrap: wrap;
}

#hc-project-profile-body-columns > div {
    flex: 1;
    display: flex;
    flex-direction: column;
    width: 25%;
    padding: 7px;
    gap: 15px;
}

#hc-project-profile-logo {
    display: inline-block;
    width: 100%;
    cursor: pointer;
    position: relative;
    border-radius: 30px;
    overflow: hidden;
}

#hc-project-profile-logo > img {
    width: 100%;
}

#hc-project-profile-logo-change {
    position: absolute;
    width: 100%;
    height: 60%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    padding: 20px;
    bottom: 0;
    color: white;
    background: linear-gradient(
        0deg,
        rgba(0, 0, 0, 0.5) 0%,
        rgba(0, 0, 0, 0) 100%
    );
}

@media (max-width: 1000px) {
    #hc-project-profile-body-columns > div {
        min-width: 50%;
        max-width: 50%;
    }
}

@media (max-width: 767px) {
    #hc-project-profile-body-columns > div {
        min-width: 100%;
        max-width: 100%;
    }
}
</style>

<script>
import ApiService from "@/apis/api.service";
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_MODAL } from "@/actions/modal";
import { OPEN_SLIDE } from "@/actions/slide";

import ApiBloc from "./blocs/api/Bloc.vue";
import CalendarBloc from "./blocs/calendar/Bloc.vue";
import CategoryBloc from "./blocs/category/Bloc.vue";
import DocumentBloc from "./blocs/document/Bloc.vue";
import FieldBloc from "./blocs/field/Bloc.vue";
import FolderBloc from "./blocs/folder/Bloc.vue";
import GroupBloc from "./blocs/group/Bloc.vue";
import ImportBloc from "./blocs/import/Bloc.vue";
import InfoBloc from "./blocs/info/Bloc.vue";
import MenuBloc from "./blocs/menu/Bloc.vue";
import OrderActionBloc from "./blocs/order-action/Bloc.vue";
import OrderStatusBloc from "./blocs/order-status/Bloc.vue";
import OrderStepBloc from "./blocs/order-step/Bloc.vue";
import ProductBloc from "./blocs/product/Bloc.vue";
import RoleBloc from "./blocs/role/Bloc.vue";
import ThreadBloc from "./blocs/thread/Bloc.vue";
import UserBloc from "./blocs/user/Bloc.vue";

export default {
    components: {
        ApiBloc,
        CalendarBloc,
        CategoryBloc,
        DocumentBloc,
        FieldBloc,
        FolderBloc,
        GroupBloc,
        ImportBloc,
        InfoBloc,
        MenuBloc,
        OrderActionBloc,
        OrderStatusBloc,
        OrderStepBloc,
        ProductBloc,
        RoleBloc,
        ThreadBloc,
        UserBloc,
    },

    data() {
        return {
            updatingLogo: false,
        };
    },

    methods: {
        async setLogo(event) {
            var reader = new FileReader();

            reader.onload = (e) => {
                this.project.thumbnail = e.target.result;
                this.$forceUpdate();
            };

            reader.readAsDataURL(event.target.files[0]);

            let data = new FormData();
            data.append("file", event.target.files[0]);

            this.updatingLogo = true;
            await ApiService.post(`project/${this.project.slug}/logo`, data);
            this.updatingLogo = false;
        },

        /**
         * Add category
         * See: @/components/category/add/Modal.vue
         */
        addCategory() {
            store.commit(OPEN_MODAL, "category-add");
        },

        /**
         * Add field
         * See: @/components/field/add/Modal.vue
         */
        addField() {
            store.commit(OPEN_MODAL, "field-add");
        },

        /**
         * Add folder
         * See: @/components/folder/add/Modal.vue
         */
        addFolder() {
            store.commit(OPEN_MODAL, "folder-add");
        },

        /**
         * Add group
         * See: @/components/group/add/Modal.vue
         */
        addGroup() {
            store.commit(OPEN_MODAL, "group-add");
        },

        /**
         * Add product
         * See: @/components/product/add/Modal.vue
         */
        addProduct() {
            store.commit(OPEN_MODAL, "product-add");
        },

        /**
         * Add role
         * See: @/components/role/add/Modal.vue
         */
        addRole() {
            store.commit(OPEN_MODAL, "role-add");
        },

        /**
         * Add thread
         * See: @/components/thread/add/Modal.vue
         */
        addThread() {
            store.commit(OPEN_MODAL, "thread-add");
        },

        /**
         * Add user
         * See: @/components/user/add/Modal.vue
         */
        addUser() {
            store.commit(OPEN_MODAL, "user-add");
        },
    },

    computed: {
        ...mapGetters(["project", "categories", "threads", "folders"]),
    },
};
</script>
