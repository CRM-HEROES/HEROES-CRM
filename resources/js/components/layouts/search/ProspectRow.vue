<template>
    <item @click="show">
        <icon class="fa fa-user" :size="28" />
        <div class="hc-item-main-content" v-text="shownField"></div>
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { SET_PROSPECT } from "@/actions/project/prospect";

export default {
    props: {
        keyword: {
            type: String,
        },
        prospect: {
            type: Object,
        },
    },

    methods: {
        show(e) {
            store.commit(SET_PROSPECT, this.prospect);
            this.$router.push({
                name: "prospect.show",
                params: {
                    project: this.project.slug,
                    prospect: this.prospect.id,
                },
            });
        },
    },

    computed: {
        ...mapGetters(["project", "fields"]),

        /**
         *
         */
        shownField() {
            const f = this.searchableFields.find((f) =>
                f.meta
                    ? this.prospect.meta &&
                      this.prospect.meta[f.slug] &&
                      this.prospect.meta[f.slug].includes(this.keyword)
                    : this.prospect[f.slug] &&
                      this.prospect[f.slug].includes(this.keyword)
            );

            return f
                ? f.meta
                    ? this.prospect.meta[f.slug]
                    : this.prospect[f.slug]
                : this.fullName;
        },

        /**
         *
         */
        searchableFields() {
            return this.fields.filter(
                (field) =>
                    field.searchable &&
                    (field.meta ||
                        (field.slug != "first_name" &&
                            field.slug != "last_name"))
            );
        },

        fullName() {
            return [this.prospect.first_name, this.prospect.last_name]
                .filter((n) => n)
                .join(" ");
        },
    },
};
</script>
