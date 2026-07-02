<template>
    <item class="hc-trash-order" tag="label">
        <div class="hc-trash-order-thumbnail hc-flex-column">
            <div class="hc-trash-order-thumbnail-content">
                <img
                    v-if="
                        order.products.length > 0 &&
                        order.products[0].images.length > 0
                    "
                    :src="order.products[0].images[0].thumbnail"
                />
            </div>
        </div>
        <div class="hc-item-main-content hc-flex-column">
            <span
                class="hc-trash-order-prospect"
                v-if="order.prospect"
                v-text="
                    [order.prospect.first_name, order.prospect.last_name]
                        .filter((n) => n)
                        .join(' ')
                "
            ></span>
            <span class="hc-trash-order-name" v-text="order.name"></span>
            <span
                class="hc-trash-order-order-status"
                v-text="order.status ? order.status.name : ''"
            ></span>
            <span class="hc-trash-order-start-date" v-text="startDate"></span>
            <span class="hc-trash-order-date" v-text="date"></span>
        </div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<style>
.hc-trash-order {
    padding: 5px !important;
    border-top: 1px solid #eee;
}

.hc-trash-order .hc-item-main-content {
    padding-left: 10px;
}

.hc-trash-order-thumbnail {
    width: 65px;
    border-radius: 5px;
    overflow: hidden;
    position: relative;
    background-color: #eee;
}

.hc-trash-order-thumbnail-content {
    text-align: center;
    width: 100%;
    padding-top: 80%;
}

.hc-trash-order-thumbnail-content > img {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
}

.hc-trash-order-order-status {
    font-size: 11px;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}

.hc-trash-order-prospect {
    color: #000000;
    white-space: normal;
    flex: 1;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.hc-trash-order-name {
    color: #000000;
    white-space: normal;
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.hc-trash-order-start-date {
    color: #12a0f3;
    font-size: 11px;
    font-weight: 500;
}

.hc-trash-order-date {
    color: #777777;
    font-size: 11px;
    margin-top: 3px;
}
</style>

<script>
import { mapGetters } from "vuex";

export default {
    props: {
        modelValue: {
            type: Array,
        },

        order: {
            type: Object,
        },
    },

    methods: {
        change(order) {
            let isChecked = order.target.checked;
            let newValue = [...this.modelValue];
            if (isChecked) {
                newValue.push(this.value);
            } else {
                newValue.splice(newValue.indexOf(this.value), 1);
            }
            this.$emit("update:modelValue", newValue);
        },
    },

    computed: {
        /**
         *
         */
        startDate() {
            return dayjs(this.order.created_at).format(
                "ddd, DD MMM YYYY - hh:mm"
            );
        },

        /**
         *
         */
        date() {
            return "Supprimé " + dayjs(this.order.deleted_at).fromNow();
        },

        /**
         *
         */
        value() {
            return this.order.id;
        },

        /**
         *
         */
        isChecked() {
            return this.modelValue.includes(this.value);
        },
    },
};
</script>
