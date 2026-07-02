<template>
    <router-link
        :to="{
            name: 'prospect',
            params: {
                project: project.slug,
            },
        }"
        class="hc-user-project"
    >
        <div class="hc-user-project-card">
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

<style>
.hc-user-project {
    width: 33.333%;
    float: left;
    padding: 10px 8px;
    display: flex;
    flex-direction: column;
    border-radius: 30px;
    gap: 5px;
    transition: all 200ms ease-out;
    opacity: 0.9;
    text-decoration: none;
}

.hc-user-project:hover {
    background-color: #0001;
    cursor: pointer;
    text-decoration: none;
    transform: scale(1.05);
    opacity: 1;
}

.hc-user-project-card {
    width: 100%;
    height: 100px;
    border-radius: 30px;
    box-shadow: 0 2px 3px rgba(0, 0, 0, 0.15);
    background-color: white;
    overflow: hidden;
    position: relative;
}

.hc-user-project-card > div {
    width: 100%;
    height: 100%;
    transform: translateX(50%);
    position: relative;
}

.hc-user-project-card > div > img {
    height: 100%;
    max-height: 100%;
    transform: translateX(-50%);
    width: auto;
}

.hc-user-project > span {
    display: inline-block;
    font-size: 13px;
    text-align: center;
    color: #555555;
    font-weight: 600;
}

.hc-user-project:hover > span {
    color: #000000;
}
</style>

<script>
export default {
    props: {
        project: {
            type: Object,
        },
    },
};
</script>
