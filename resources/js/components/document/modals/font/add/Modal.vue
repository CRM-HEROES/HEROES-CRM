<template>
    <modal
        name="document-font-add"
        :title="$t('document.font.new.title')"
        :width="300"
        @open="font = newFont()"
    >
        <form
            class="hc-flex-column"
            style="height: 100%"
            @submit.prevent="storeFont"
        >
            <div class="hc-flex-column">
                <v-field
                    :label="$t('document.font.new.choose_file')"
                    required
                    v-slot="{ label }"
                >
                    <input
                        :placeholder="label"
                        type="file"
                        @change="setFile"
                        accept="font/ttf"
                        required
                    />
                </v-field>
                <v-field
                    :label="$t('document.font.new.name')"
                    required
                    v-slot="{ label }"
                >
                    <input :placeholder="label" v-model="font.name" required />
                </v-field>
                <v-field :label="$t('document.font.new.type.title')" required>
                    <select v-model="font.weight" required>
                        <option
                            value="normal"
                            v-text="$t('document.font.new.type.normal')"
                        ></option>
                        <option
                            value="bold"
                            v-text="$t('document.font.new.type.bold')"
                        ></option>
                    </select>
                </v-field>
                <v-field :label="$t('document.font.new.style.title')" required>
                    <select v-model="font.style" required>
                        <option
                            value="normal"
                            v-text="$t('document.font.new.style.normal')"
                        ></option>
                        <option
                            value="italic"
                            v-text="$t('document.font.new.style.italic')"
                        ></option>
                    </select>
                </v-field>
            </div>
            <buttons>
                <button v-text="$t('add')"></button>
            </buttons>
            <loading :loading="addingFont" />
        </form>
    </modal>
</template>

<script>
import store from "@/store";

// Actions
import {
    ADD_DOCUMENT_FONT,
    FETCH_DOCUMENT_FONTS,
} from "@/actions/project/document";
import { CLOSE_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            font: this.newFont(),
            addingFont: false,
        };
    },

    methods: {
        /**
         *
         */
        newFont() {
            return {
                file: null,
                name: "",
                weight: "normal",
                style: "normal",
            };
        },

        /**
         *
         */
        setFile(e) {
            this.font.file = e.target.files[0];
            this.font.name = this.font.file.name.substring(
                0,
                this.font.file.name.lastIndexOf(".")
            );

            if (this.font.file.name.toLowerCase().indexOf("bold") >= 0) {
                this.font.weight = "bold";
            }

            if (this.font.file.name.toLowerCase().indexOf("italic") >= 0) {
                this.font.style = "italic";
            }
        },

        /**
         *
         */
        async storeFont() {
            this.addingFont = true;

            var formData = new FormData();
            for (const i in this.font) {
                formData.append(i, this.font[i]);
            }

            try {
                await store.dispatch(ADD_DOCUMENT_FONT, formData);
                store.dispatch(FETCH_DOCUMENT_FONTS);
            } finally {
                this.addingFont = false;
                store.commit(CLOSE_MODAL);
            }
        },
    },
};
</script>
