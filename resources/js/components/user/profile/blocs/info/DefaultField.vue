<template>
    <label class="hc-user-profile-user-field">
        <div class="hc-user-profile-user-field-label" v-text="field.name"></div>

        <div class="hc-user-profile-user-field-input">
            <street-cell
                v-if="field.slug == 'street'"
                :user="user"
                :field="field.slug"
                :placeholder="field.name"
            />
            <default-cell
                v-else
                :user="user"
                :field="field.slug"
                :placeholder="field.name"
                :disabled="!superAdmin && !can('all')"
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
}

.hc-user-profile-user-field-label {
    padding: 0 10px 0 0;
    min-width: 60px;
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    line-height: 26px;
    color: #555;
    font-weight: 500;
}

.hc-user-profile-user-field-input {
    flex: 1;
    position: relative;
}

.hc-user-profile-user-field-input > .hc-default-cell-label > input {
    height: 26px;
}

.hc-user-profile-user-field-input > .hc-default-cell-label > span {
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
        ...mapGetters(["superAdmin", "can"]),
    },
};
</script>
