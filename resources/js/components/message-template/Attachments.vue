<template>
    <div class="hc-message-template-attachments">
        <v-field label="Pièces jointes">
            <input type="file" multiple :disabled="disabled" @change="add" />
        </v-field>
        <p class="hc-message-template-attachments-hint">10 fichiers maximum, 10 Mo par fichier.</p>
        <item v-for="(attachment, index) in attachments" :key="attachment.id || index">
            <icon class="fa fa-paperclip" />
            <div class="hc-item-main-content" v-text="attachment.name"></div>
            <button
                type="button"
                :disabled="disabled"
                :aria-label="'Retirer ' + attachment.name"
                @click="remove(index)"
            >×</button>
        </item>
        <p v-if="error" role="alert" class="hc-message-template-error" v-text="error"></p>
    </div>
</template>

<script>
export default {
    props: {
        attachments: { type: Array, default: () => [] },
        disabled: { type: Boolean, default: false },
    },
    emits: ["update:attachments"],
    data() {
        return { error: "" };
    },
    methods: {
        add(event) {
            const files = Array.from(event.target.files || []);
            event.target.value = "";
            if (this.disabled) return;
            if (this.attachments.length + files.length > 10 || files.some(file => file.size > 10 * 1024 * 1024)) {
                this.error = "Vous pouvez joindre 10 fichiers maximum de 10 Mo chacun.";
                return;
            }
            this.error = "";
            this.$emit("update:attachments", [...this.attachments, ...files]);
        },
        remove(index) {
            if (this.disabled) return;
            this.error = "";
            this.$emit("update:attachments", this.attachments.filter((attachment, position) => position !== index));
        },
    },
};
</script>

<style>
.hc-message-template-attachments-hint { padding: 0 10px; color: #777; font-size: 11px; }
.hc-message-template-error { padding: 5px 10px; color: #b42318; }
</style>
