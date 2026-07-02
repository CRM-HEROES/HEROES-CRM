<template>
    <item class="hc-menu-icon" tag="label">
        <div class="hc-menu-icon-thumbnail hc-flex-column">
            <div class="hc-menu-icon-thumbnail-content">
                <img v-if="thumbnail" :src="thumbnail" />
                <i v-else class="fa fa-icons"></i>
            </div>
        </div>
        <div class="hc-item-main-content hc-flex-column">
            <div class="hc-menu-icon-prospect" v-text="menuIcon.name"></div>
            <div class="hc-menu-icon-name" v-text="menuIcon.group"></div>
        </div>
        <icon
            v-if="thumbnail"
            tag="a"
            class="fa fa-trash icon-red"
            @click.prevent.stop="remove"
        />
        <input type="file" @change="update" />
        <loading :loading="uploading || removing" />
    </item>
</template>

<style>
.hc-menu-icon {
    padding: 5px !important;
}

.hc-menu-icon .hc-item-main-content {
    padding: 0 5px;
}

.hc-menu-icon-thumbnail {
    width: 50px;
    border-radius: 5px;
    overflow: hidden;
    position: relative;
    background-color: #eee;
}

.hc-menu-icon-thumbnail-content {
    text-align: center;
    width: 100%;
    padding-top: 80%;
}

.hc-menu-icon-thumbnail-content > img,
.hc-menu-icon-thumbnail-content > i {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
    line-height: 40px;
    font-size: 15px;
}

.hc-menu-icon-prospect {
    font-weight: 500;
    overflow: hidden;
    text-overflow: ellipsis;
}

.hc-menu-icon-name {
    font-size: 11px;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<script>
import ApiService from "@/apis/api.service";
import { API_URL } from "@/apis/common";
import { mapGetters } from "vuex";
import store from "@/store";

import {
    UPDATE_PROJECT_USER_SETTING,
    REMOVE_PROJECT_USER_SETTING,
} from "@/actions/project/user/setting";

export default {
    props: {
        menuIcon: {
            type: Object,
        },
    },

    data() {
        return {
            uploading: false,
            removing: false,
        };
    },

    methods: {
        update(event) {
            const params = new FormData();
            params.append("file", event.target.files[0]);

            // upload setting
            var settings = {
                // header multipart form data
                headers: {
                    "content-type": "multipart/form-data",
                },
            };

            this.uploading = true;

            try {
                ApiService.post(
                    "project/" +
                        this.project.slug +
                        "/menu-icon/" +
                        this.menuIcon.key,
                    params,
                    settings
                );

                store.commit(UPDATE_PROJECT_USER_SETTING, {
                    key: this.settingKey,
                    value: new Date().toISOString(),
                });
            } finally {
                this.uploading = false;
            }
        },

        remove() {
            this.removing = true;

            try {
                store.commit(REMOVE_PROJECT_USER_SETTING, this.settingKey);
                ApiService.delete(
                    "project/" +
                        this.project.slug +
                        "/menu-icon/" +
                        this.menuIcon.key
                );
            } finally {
                this.removing = false;
            }
        },
    },

    computed: {
        ...mapGetters(["project", "projectUserSettingsMenuIconUrl"]),

        /**
         *
         */
        settingKey() {
            return "menu-icon." + this.menuIcon.key;
        },

        /**
         *
         */
        thumbnail() {
            return this.projectUserSettingsMenuIconUrl(this.menuIcon.key);
        },
    },
};
</script>
