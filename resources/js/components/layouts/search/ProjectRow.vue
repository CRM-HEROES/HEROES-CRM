<template>
    <item @click="show">
        <icon>
            <img
                :src="
                    project.thumbnail ? project.thumbnail : '/images/logo.png'
                "
                :alt="project.name"
            />
        </icon>
        <div class="hc-item-main-content" v-text="project.name"></div>
    </item>
</template>

<script>
export default {
    props: {
        project: {
            type: Object,
        },
    },

    methods: {
        async show(e) {
            this.$router.push({
                name: "project.show",
                params: {
                    project: this.project.slug,
                },
            });
        },
    },
};
</script>
