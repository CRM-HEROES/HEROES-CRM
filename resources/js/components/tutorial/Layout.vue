<template>
    <div class="hc-flex-column" style="height: 100%">
        <item tag="label">
            <icon class="fa fa-eye-slash" :style="style" />
            <div
                class="hc-item-main-content"
                v-text="$t('setting.tutorials.do_not_show_anymore')"
            ></div>
            <checkbox v-model="doNotShowAnymore" />
        </item>
        <search v-model="tutorialKeyword" />
        <item-list
            class="hc-flex-1"
            style="height: 100%; overflow: auto"
            padding="12px"
            v-tuto="{
                key: 'tutorial',
                name: $t('tutorial.tutorial.name'),
                body: $t('tutorial.tutorial.body'),
                timeout: 500,
            }"
        >
            <tutorial-row
                v-for="tutorial in filteredTutorials"
                :key="tutorial.key"
                :tutorial="tutorial"
                @click.prevent="removeTutorial(tutorial)"
            />
        </item-list>
        <buttons>
            <a @click.prevent="removeTutorials">
                <i class="fa fa-eye"></i>
                {{ "Tout réafficher" }}
            </a>
        </buttons>
    </div>
</template>

<script>
import TutorialRow from "./TutorialRow.vue";

export default {
    components: { TutorialRow },

    data() {
        return {
            tutorialKeyword: "",
            tutorials: [],
            doNotShowAnymoreKey: "hc-tuto.do-not-show-anymore",
        };
    },

    created() {
        this.getTutorials();
    },

    methods: {
        getTutorials() {
            this.tutorials = document.cookie
                .split("; ")
                .map((c) => {
                    const [key, name] = c.split("=");
                    return { key, name };
                })
                .filter((tutorial) => tutorial.key.indexOf("hc-tuto.") == 0)
                .sort((a, b) => (a.name > b.name ? 1 : -1));
        },

        setCookie(key, value, expires, path) {
            document.cookie = `${key}=${value}; expires=${expires}; path=${path}`;
        },

        removeCookie(key) {
            document.cookie = this.setCookie(
                key,
                "",
                "Thu, 01 Jan 1970 00:00:00 GMT",
                "/"
            );
        },

        removeTutorial(tutorial) {
            this.removeCookie(tutorial.key);
            this.getTutorials();
        },

        removeTutorials() {
            this.tutorials.forEach((tutorial) => {
                this.removeCookie(tutorial.key);
            });
            this.getTutorials();
        },
    },

    computed: {
        filteredTutorials() {
            const keyword = removeStringAccent(this.tutorialKeyword);

            return this.tutorials.filter(
                (tutorial) =>
                    removeStringAccent(tutorial.name).indexOf(keyword) >= 0 &&
                    tutorial.key != this.doNotShowAnymoreKey
            );
        },

        doNotShowAnymore: {
            get() {
                return (
                    this.tutorials.find(
                        (tutorial) => tutorial.key == this.doNotShowAnymoreKey
                    ) !== undefined
                );
            },
            set(value) {
                if (value) {
                    let date = new Date();
                    date.setFullYear(date.getFullYear() + 10);
                    this.setCookie(
                        this.doNotShowAnymoreKey,
                        1,
                        date.toGMTString(),
                        "/"
                    );
                } else {
                    const cookie =
                        this.tutorials.find(
                            (tutorial) =>
                                tutorial.key == this.doNotShowAnymoreKey
                        ) !== undefined;
                    if (cookie) {
                        this.removeCookie(this.doNotShowAnymoreKey);
                    }
                }

                this.getTutorials();
            },
        },
    },
};
</script>
