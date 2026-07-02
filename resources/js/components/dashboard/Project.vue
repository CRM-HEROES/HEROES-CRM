<template>
    <router-link
        :to="{
            name: 'prospect',
            params: {
                project: project.slug,
            },
        }"
        class="hc-dashboard-project"
    >
        <div class="hc-dashboard-project-card">
            <div>
                <img
                    :src="
                        project.thumbnail
                            ? project.thumbnail
                            : '/images/logo.png'
                    "
                    :alt="project.name"
                />
            </div>
        </div>
        <span v-text="project.name"></span>
    </router-link>
</template>

<script>
export default {
    props: {
        project: {
            type: Object,
        },
    },
};
</script>
