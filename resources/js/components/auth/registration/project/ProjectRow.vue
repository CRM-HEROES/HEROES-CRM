<template>
    <div class="hc-user-registration-project-row">
        <div class="hc-user-registration-project-logo">
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
        <div class="hc-user-registration-project-info">
            <span
                class="hc-user-registration-project-title"
                v-text="project.name"
            >
            </span>
            <p
                v-if="project.description"
                class="hc-user-registration-project-description"
                v-text="project.description"
            ></p>
            <div class="hc-user-registration-project-creator-role">
                <span
                    class="hc-user-registration-project-creator"
                    v-text="
                        'Par ' +
                        (project.creator ? project.creator.name : 'HeroesCRM')
                    "
                >
                </span>
                <span
                    v-if="project.roles.length > 0"
                    class="hc-user-registration-project-role"
                    v-text="' - en tant que ' + project.roles.join(', ')"
                >
                </span>
            </div>
        </div>
        <i class="fa fa-caret-right"></i>
    </div>
</template>

<style>
.hc-user-registration-project-row {
    display: flex;
    flex-direction: row;
    padding: 16px 20px;
    gap: 16px;
    border: 1px solid #eee;
    border-radius: 4px;
    margin-bottom: -1px;
    align-items: center;
    cursor: pointer;
}

.hc-user-registration-project-row:hover {
    background-color: #f5f5f5;
}

.hc-user-registration-project-logo {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #ddd;
    overflow: hidden;
}

.hc-user-registration-project-logo > div {
    width: 100%;
    height: 100%;
    transform: translateX(50%);
    position: relative;
}

.hc-user-registration-project-logo > div > img {
    height: 100%;
    max-height: 100%;
    transform: translateX(-50%);
    width: auto;
}

.hc-user-registration-project-info {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.hc-user-registration-project-title {
    font-size: 14px;
    font-weight: bold;
}

.hc-user-registration-project-description {
    font-size: 12px;
    color: #6c6c6c;
    margin-top: 10px;
    margin-bottom: 4px;
}

.hc-user-registration-project-creator-role {
    font-size: 12px;
    font-weight: bold;
}

.hc-user-registration-project-creator {
    color: #2aaed7;
}

.hc-user-registration-project-role {
}

.hc-user-registration-project-row > i {
    font-size: 15px;
    color: #aaaaaa;
}
</style>

<script>
export default {
    props: {
        project: {
            type: String,
        },
    },

    methods: {
        change(event) {
            this.$emit("change", event);
            this.$emit("update:modelValue", event.target.value);
        },
    },
};
</script>
