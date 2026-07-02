<template>
    <item
        tag="router-link"
        :to="{
            name: 'user.show',
            params: {
                project: project.slug,
                user: user.user_id,
            },
        }"
    >
        <icon class="fa fa-user" />
        <div class="hc-item-main-content" v-text="user.name"></div>
    </item>
</template>

<script>
export default {
    props: {
        user: {
            type: Object,
        },
        project: {
            type: Object,
        },
    },
};
</script>
