<template>
    <label class="hc-user-profile-user-field">
        <div class="hc-user-profile-user-field-label" v-text="field.name"></div>

        <div class="hc-user-profile-user-field-input">
            <street-cell
                v-if="field.slug == 'street'"
                :user="user"
                :field="field.slug"
                :disabled="false"
            />
            <default-cell
                v-else-if="
                    field.slug != 'password' ||
                    user.creator_id == authUser.id ||
                    user.id == authUser.id ||
                    authUser.is_super_admin
                "
                :user="user"
                :field="field.slug"
                :placeholder="field.name"
                :disabled="false"
            />
        </div>
    </label>
</template>

<style scoped>
.hc-user-profile-user-field {
    width: 100%;
    min-height: 26px;
    display: flex;
    flex-direction: row;
    font-size: 12px;
    overflow: hidden;
    padding: 1px 0;
}

.hc-user-profile-user-field-label {
    padding: 0 10px 0 0;
    width: 140px;
    min-width: 60px;
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    line-height: 26px;
    color: #555;
}

.hc-user-profile-user-field-input {
    flex: 1;
    position: relative;
    border: 1px solid #ddd;
}

.hc-user-profile-user-field-input input {
    height: 26px;
}

.hc-user-profile-user-field-input .hc-default-cell-label {
    height: 26px;
}

.hc-user-profile-user-field-input .hc-default-cell-label > span {
    line-height: 26px;
}
</style>

<script>
import { mapGetters } from "vuex";
import DefaultCell from "@/components/user/table/cell/DefaultCell.vue";
import StreetCell from "@/components/user/table/cell/StreetCell.vue";

export default {
    components: {
        DefaultCell,
        StreetCell,
    },

    props: {
        /**
         * Field
         */
        field: {
            type: Object,
        },

        /**
         */
        user: {
            type: Object,
        },
    },

    computed: {
        ...mapGetters("auth", { authUser: "user" }),
    },
};
</script>
