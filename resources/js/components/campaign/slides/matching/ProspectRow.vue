<template>
    <item>
        <icon class="fa fa-user" icon-size="11" :size="30" />
        <div class="hc-item-main-content" v-text="fullName"></div>
    </item>
</template>

<script>
export default {
    props: {
        prospect: {
            type: Object,
        },
    },

    computed: {
        fullName() {
            return [this.prospect.first_name, this.prospect.last_name]
                .filter((e) => e)
                .join(" ");
        },
    },
};
</script>
