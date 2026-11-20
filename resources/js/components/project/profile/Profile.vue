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
                <div
                    v-for="(column, i) in projectUserSettingsProjectProfile"
                    :key="i"
                >
                    <label v-if="i == 0" id="hc-project-profile-logo">
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
                    <draggable
                        tag="div"
                        class="hc-project-profile-body-column"
                        :list="column"
                        :disabled="!movable"
                        group="project-profile-bloc"
                        :item-key="blocKey"
                        handle=".bloc-handle"
                        @end="setBlocColumn"
                    >
                        <template #item="{ element }">
                            <profile-bloc :bloc="element" />
                        </template>
                    </draggable>
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

.hc-project-profile-body-column {
    display: flex;
    flex-direction: column;
    gap: 15px;
    min-height: 50px;
    flex: 1;
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
import {
    GET_PROJECT_USER_SETTING,
    UPDATE_PROJECT_USER_SETTING,
} from "@/actions/project/user/setting";

import ProfileBloc from "./ProfileBloc.vue";

export default {
    components: {
        ProfileBloc,
    },

    created() {
        store.dispatch(GET_PROJECT_USER_SETTING, { key: "project-profile" });
    },

    data() {
        return {
            updatingLogo: false,
            movable: deviceType() == "desktop",
        };
    },

    methods: {
        blocKey(bloc) {
            return bloc.type;
        },

        /**
         * Save blocs order
         */
        setBlocColumn() {
            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "project-profile",
                value: [...this.projectUserSettingsProjectProfile],
            });
        },

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
        ...mapGetters([
            "project",
            "categories",
            "threads",
            "folders",
            "projectUserSettingsProjectProfile",
        ]),
    },
};
</script>
