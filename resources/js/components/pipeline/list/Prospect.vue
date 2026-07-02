<template>
    <router-link class="hc-pipeline-prospect" :to="url">
        <div class="hc-pipeline-prospect-content">
            <div class="hc-pipeline-prospect-name" v-text="prospectName"></div>
            <div class="hc-pipeline-prospect-resume">
                <div
                    v-for="field in resumeFields"
                    :key="field"
                    v-text="
                        field.indexOf('meta->') == 0
                            ? prospect.meta[field.replace('meta->', '')]
                            : prospect[field]
                    "
                ></div>
            </div>
        </div>
        <icon class="fa fa-eye icon-blue" size="28" icon-size="14" />
    </router-link>
</template>

<style>
.hc-pipeline-prospect {
    display: flex;
    flex-direction: row;
    width: 100%;
    height: auto;
    border-radius: 2px;
    background-color: #ffffff;
    /* box-shadow: 0 2px 4px -2px #0004; */
    margin-top: -1px;
    border: 1px solid #e5e5e5;
    align-items: center;
    justify-content: center;
    padding: 8px 12px;
    cursor: pointer;
    text-decoration: none;
}

.hc-pipeline-prospect:hover {
    background-color: #fafafa;
}

.hc-pipeline-prospect-content {
    display: flex;
    flex-direction: column;
    width: 100%;
    height: auto;
}

.hc-pipeline-prospect-name {
    font-size: 12px;
    font-weight: bold;
    color: #000;
}

.hc-pipeline-prospect-resume {
    font-size: 12px;
    color: #777;
    width: 100%;
}
</style>

<script>
import { mapGetters } from "vuex";

export default {
    props: {
        prospect: {
            type: Object,
        },
    },

    data() {
        return {
            resumeFields: ["email", "mobile_phone_number"],
        };
    },

    computed: {
        ...mapGetters(["project"]),

        prospectName() {
            return [this.prospect.first_name, this.prospect.last_name]
                .filter((e) => e)
                .join(" ");
        },

        url() {
            const routeData = this.$router.resolve({
                name: "prospect.show",
                params: {
                    project: this.project.slug,
                    prospect: this.prospect.id,
                },
            });

            return routeData.href;
        },
    },
};
</script>
