<template>
    <div class="hc-prospect-message-selected-prospect">
        <div
            class="hc-prospect-message-selected-prospect-name"
            v-text="
                [prospect.first_name, prospect.last_name]
                    .filter((n) => n)
                    .join(' ')
            "
        ></div>
        <icon
            class="fa fa-times icon-red"
            size="30"
            @click.prevent="$emit('remove', prospect)"
        />
    </div>
</template>

<style>
.hc-prospect-message-selected-prospect {
    float: left;
    margin-right: 5px;
    margin-bottom: 5px;
    display: flex;
    flex-direction: row;
    padding: 0 0 0 10px;
    background-color: #eee;
    align-items: center;
    border-radius: 3px;
}

.hc-prospect-message-selected-prospect .fa-times {
    cursor: pointer;
}
</style>

<script>
export default {
    props: {
        prospect: {
            type: Object,
        },
    },
};
</script>
