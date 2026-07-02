<template>
    <tr class="hc-table-footer">
        <td class="fixed">
            <label class="hc-table-selector"></label>

            <div class="hc-table-row-options">
                <a
                    v-for="menu in projectUserSettingsProspectsTableMenus"
                    :key="menu.key"
                ></a>
            </div>

            <!-- List of fixed columns -->
            <div class="hc-table-fixed-cells">
                <footer-cell
                    v-for="column in fixedColumns"
                    :key="column.key"
                    tag="div"
                    :column="column"
                />
            </div>
        </td>
        <footer-cell
            v-for="column in notFixedColumns"
            :key="column.key"
            tag="td"
            :column="column"
        />
    </tr>
</template>

<script>
import { mapGetters } from "vuex";

import FooterCell from "./FooterCell.vue";

export default {
    components: {
        FooterCell,
    },

    props: {
        /**
         * List of fixed columns
         */
        fixedColumns: {
            type: Array,
        },

        /**
         * List of none fixed columns
         */
        notFixedColumns: {
            type: Array,
        },
    },

    computed: {
        ...mapGetters(["projectUserSettingsProspectsTableMenus"]),
    },
};
</script>
