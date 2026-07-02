<template>
    <item class="hc-trash-file" tag="label">
        <div class="hc-trash-file-thumbnail hc-flex-column">
            <div class="hc-trash-file-thumbnail-content">
                <img :src="file.thumbnail" />
            </div>
        </div>
        <div class="hc-item-main-content hc-flex-column">
            <div
                class="hc-trash-file-prospect"
                v-text="
                    [file.prospect.first_name, file.prospect.last_name]
                        .filter((n) => n)
                        .join(' ')
                "
            ></div>
            <div class="hc-trash-file-name" v-text="file.name"></div>
            <div class="hc-trash-file-date" v-text="date"></div>
        </div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<style>
.hc-trash-file {
    padding: 5px !important;
}

.hc-trash-file .hc-item-main-content {
    padding: 0 5px;
}

.hc-trash-file-thumbnail {
    width: 65px;
    border-radius: 5px;
    overflow: hidden;
    position: relative;
    background-color: #eee;
}

.hc-trash-file-thumbnail-content {
    text-align: center;
    width: 100%;
    padding-top: 80%;
}

.hc-trash-file-thumbnail-content > img {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
}

.hc-trash-file-prospect {
    font-weight: 500;
    overflow: hidden;
    text-overflow: ellipsis;
}

.hc-trash-file-name {
    font-size: 11px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.hc-trash-file-date {
    margin-top: 3px;
    font-size: 11px;
    color: #888;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<script>
export default {
    props: {
        modelValue: {
            type: Array,
        },

        file: {
            type: Object,
        },
    },

    methods: {
        change(event) {
            let isChecked = event.target.checked;
            let newValue = [...this.modelValue];
            if (isChecked) {
                newValue.push(this.value);
            } else {
                newValue.splice(newValue.indexOf(this.value), 1);
            }
            this.$emit("update:modelValue", newValue);
        },
    },

    computed: {
        /**
         *
         */
        date() {
            return "Supprimé " + dayjs(this.file.deleted_at).fromNow();
        },

        /**
         *
         */
        value() {
            return this.file.id;
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
