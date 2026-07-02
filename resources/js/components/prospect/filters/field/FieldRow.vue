<template>
    <item class="hc-prospects-table-filter-field-row" tag="label">
        <div
            class="hc-prospects-table-filter-field-row-name"
            v-text="field.name"
        ></div>
        <input
            class="hc-prospects-table-filter-field-row-input"
            v-model.lazy="value"
            :disabled="
                prospectsParamExists(withKey) &&
                prospectsParamValue(withKey) == 0
            "
        />
        <icon
            :class="[
                'fa',
                'fa-check',
                prospectsParamExists(withKey)
                    ? prospectsParamValue(withKey) == 1
                        ? 'icon-green'
                        : 'icon-red'
                    : 'icon-grey',
            ]"
            @click="toggleWithField"
        />
    </item>
</template>

<style>
.hc-prospects-table-filter-field-row {
}
.hc-prospects-table-filter-field-row-name {
    flex: 1;
}
.hc-prospects-table-filter-field-row-input {
    padding: 0 4px;
    /* flex: 1; */
    width: 100px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    FETCH_PROSPECTS,
    ADD_PROSPECT_PARAMS,
    REMOVE_PROSPECT_PARAMS,
} from "@/actions/project/prospect";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_FIELD } from "@/actions/project/field";

export default {
    props: {
        field: {
            type: Object,
        },
    },

    methods: {
        /**
         *
         */
        async toggleWithField() {
            try {
                if (!this.prospectsParamExists(this.withKey)) {
                    await store.commit(ADD_PROSPECT_PARAMS, {
                        key: this.withKey,
                        value: 1,
                        multiple: false,
                    });
                } else if (this.prospectsParamValue(this.withKey) == 1) {
                    await store.commit(ADD_PROSPECT_PARAMS, {
                        key: this.withKey,
                        value: 0,
                        multiple: false,
                    });
                } else {
                    await store.commit(REMOVE_PROSPECT_PARAMS, {
                        key: this.withKey,
                    });
                }

                // Update prospects list
                store.dispatch(FETCH_PROSPECTS);
            } finally {
            }
        },

        edit() {
            store.commit(OPEN_MODAL, "field-update");
            store.commit(SET_FIELD, this.field);
        },
    },

    computed: {
        ...mapGetters(["prospectsParamExists", "prospectsParamValue", "can"]),

        value: {
            get: function () {
                return this.prospectsParamExists(this.filterKey)
                    ? this.prospectsParamValue(this.filterKey)
                    : "";
            },
            set: function (value) {
                if (value) {
                    store.commit(ADD_PROSPECT_PARAMS, {
                        key: this.filterKey,
                        value: value,
                    });
                } else {
                    store.commit(REMOVE_PROSPECT_PARAMS, {
                        key: this.filterKey,
                    });
                }
                store.dispatch(FETCH_PROSPECTS);
            },
        },

        /**
         *
         */
        filterKey() {
            return "field_" + this.field.slug;
        },

        /**
         *
         */
        withKey() {
            return "withField_" + this.field.slug;
        },
    },
};
</script>
