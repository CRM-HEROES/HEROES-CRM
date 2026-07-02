<template>
    <item class="hc-campaign-available-item hc-campaign-available-item">
        <icon :class="item.icon ? item.icon : 'fa fa-person-digging'" />
        <div class="hc-item-main-content hc-flex-column">
            <span
                class="hc-campaign-available-item-name"
                v-text="item.name"
            ></span>
            <span class="hc-campaign-available-item-type" v-text="type"></span>
        </div>
    </item>
</template>

<style>
.hc-campaign-available-item {
}

.hc-campaign-available-item-name {
    /*font-weight: 600;*/
}

.hc-campaign-available-item-type {
    color: #0ea0cf;
}
</style>

<script>
export default {
    props: {
        item: {
            type: Object,
        },
    },

    computed: {
        /**
         *
         */
        type() {
            if (this.item.type) {
                return this.item.type;
            }

            return "Action";
        },
    },
};
</script>
