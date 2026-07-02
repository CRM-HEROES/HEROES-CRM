<template>
    <item
        :class="[
            'hc-prospect-import-duplicate',
            mapping ? 'hc-prospect-import-duplicate-mapped' : '',
        ]"
    >
        <label class="hc-prospect-import-duplicate-check">
            <input type="checkbox" :checked="isChecked" @click="change" />
            <span></span>
        </label>
        <div
            class="hc-item-main-content"
            @click.stop="showDuplicateProspect"
            v-text="duplicateFullName"
        ></div>
        <div
            @click.stop="showOriginalProspect"
            class="hc-prospect-import-duplicate-from"
            v-text="prospectFullName"
        ></div>
    </item>
</template>

<style>
.hc-prospect-import-duplicate-check {
    cursor: pointer;
    height: 36px;
    line-height: 36px;
    position: relative;
    text-align: center;
    width: 36px;
}

.hc-prospect-import-duplicate-check > input {
    cursor: pointer;
    height: 100%;
    left: 0;
    opacity: 0;
    position: absolute;
    top: 0;
    width: 100%;
}

.hc-prospect-import-duplicate-check > span {
    border: 1px solid #777;
    border-radius: 3px;
    display: inline-block;
    height: 12px;
    vertical-align: middle;
    width: 12px;
}

.hc-prospect-import-duplicate-check > input:checked + span {
    background-color: #7939b8;
    border: none;
}

.hc-prospect-import-duplicate > .hc-item-main-content {
    color: #333333;
}
.hc-prospect-import-duplicate > .hc-icon {
    color: #bbbbbb;
}
.hc-prospect-import-duplicate-mapped > .hc-icon {
    color: #333333;
}
.hc-prospect-import-duplicate-mapped > .hc-item-main-content {
    color: #111111;
}
.hc-prospect-import-duplicate-from {
    color: #2891d2;
    max-width: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
    padding-right: 10px;
}
</style>

<script>
import { mapGetters } from "vuex";

export default {
    props: {
        index: {
            type: Number,
        },

        modelValue: {
            type: Array,
        },

        duplicate: {
            type: Object,
        },

        prospect: {
            type: Object,
        },
    },

    methods: {
        change(event) {
            const index = this.index;
            const shift = event.shiftKey;
            const checked = event.currentTarget.checked;

            let newValue = [...this.modelValue];
            if (checked) {
                newValue.push(this.value);
            } else {
                newValue.splice(newValue.indexOf(this.value), 1);
            }
            this.$emit("update:modelValue", newValue);
            this.$emit("duplicate-checked", index, shift, checked);
        },

        showDuplicateProspect() {
            const routeData = this.$router.resolve({
                name: "prospect.show",
                params: {
                    project: this.project.slug,
                    prospect: this.duplicate.id,
                },
            });
            window.open(routeData.href, "_blank");
        },

        showOriginalProspect() {
            const routeData = this.$router.resolve({
                name: "prospect.show",
                params: {
                    project: this.project.slug,
                    prospect: this.prospect.id,
                },
            });
            window.open(routeData.href, "_blank");
        },
    },

    computed: {
        ...mapGetters(["project"]),

        duplicateFullName() {
            return [this.duplicate.first_name, this.duplicate.last_name].join(
                " "
            );
        },
        prospectFullName() {
            return [this.prospect.first_name, this.prospect.last_name].join(
                " "
            );
        },

        /**
         *
         */
        value() {
            return this.duplicate.id;
        },

        /**
         *
         */
        isChecked() {
            return this.modelValue.includes(this.value);
        },
    },
};
</script>
