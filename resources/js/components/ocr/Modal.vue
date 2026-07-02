<template>
    <modal
        :title="$t('prospect.scan.title')"
        name="prospects-table-ocr"
        :width="500"
        @closed="stopCamera"
    >
        <tab :count="2" @tab:change="(t) => (tab = t)">
            <template #1>{{ $t("prospect.scan.import") }}</template>
            <template #2>{{ $t("prospect.scan.scan") }}</template>
        </tab>
        <tab-layout :count="2" :tab="tab" class="hc-flex-1">
            <template #1>
                <div id="prospects-table-ocr-import-container">
                    <div id="prospects-table-ocr-import-content">
                        <div
                            id="prospects-table-ocr-imports"
                            @dragenter.prevent
                            @dragover.prevent
                            @dragleave.prevent
                            @drop.prevent="dropFiles"
                        >
                            <ocr-type
                                v-for="ocrType in ocrTypes"
                                :key="ocrType.api + '.' + ocrType.type"
                                :ocr-type="ocrType"
                                @add-files="addFiles"
                            />
                            <uploading-file-row
                                v-for="file in uploadingFiles"
                                :key="file.file.name"
                                :file="file.file"
                                :ocr-type="file.ocrType"
                                @file-uploaded="onFileUploaded"
                            />
                        </div>
                    </div>
                </div>
            </template>
            <template #2>
                <div id="prospects-table-ocr-scan-container">
                    <div id="prospects-table-ocr-scan-content">
                        <video ref="video" autoplay></video>
                        <canvas ref="canvas" style="display: none"></canvas>
                    </div>
                </div>
                <buttons>
                    <a @click.prevent="capturePhoto"> Scanner </a>
                    <loading :loading="sendingImage" />
                </buttons>
            </template>
        </tab-layout>
    </modal>
</template>

<style>
#prospects-table-ocr-scan-container,
#prospects-table-ocr-import-container {
    width: 100%;
    border-radius: 5px;
    overflow: hidden;
    position: relative;
}

#prospects-table-ocr-scan-content,
#prospects-table-ocr-import-content {
    width: 100%;
    padding-top: 80%;
}

#prospects-table-ocr-scan-content > video,
#prospects-table-ocr-imports {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
}

#prospects-table-ocr-imports {
    overflow: auto;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import ApiService from "@/apis/api.service";

import { CLOSE_MODAL } from "@/actions/modal";

// Components
import UploadingFileRow from "./UploadingFileRow.vue";
import OcrType from "./OcrType.vue";

export default {
    components: {
        UploadingFileRow,
        OcrType,
    },

    data() {
        return {
            tab: 0,
            scan: false,
            sendingImage: false,
            uploadedFiles: [],
            uploadingFiles: [],
        };
    },

    methods: {
        async startCamera() {
            try {
                // Get the list of available media devices
                const devices = await navigator.mediaDevices.enumerateDevices();

                // Find the back camera source
                const backCamera = devices.find(
                    (device) =>
                        device.kind === "videoinput" &&
                        device.label.includes("back")
                );

                if (backCamera) {
                    this.$refs.video.srcObject =
                        await navigator.mediaDevices.getUserMedia({
                            video: { deviceId: backCamera.deviceId },
                        });
                } else {
                    this.$refs.video.srcObject =
                        await navigator.mediaDevices.getUserMedia({
                            video: true,
                        });
                }

                this.$refs.video.play();
            } catch (error) {
                console.error(
                    $t("prospect.scan.error.camera_access", { error })
                );
            }
        },

        // Function to stop the camera
        stopCamera() {
            try {
                const stream = this.$refs.video.srcObject;
                const tracks = stream.getTracks();

                tracks.forEach((track) => {
                    track.stop();
                });

                this.$refs.video.srcObject = null;
            } catch (error) {
                console.error(
                    this.$t("prospect.scan.error.camera_stop", { error })
                );
            }
        },

        capturePhoto() {
            if (!this.$refs.video.srcObject) {
                console.error(this.$t("prospect.scan.error.camera_disabled"));
                return;
            }

            // Set the canvas dimensions to match the video feed
            this.$refs.canvas.width = this.$refs.video.videoWidth;
            this.$refs.canvas.height = this.$refs.video.videoHeight;

            const ctx = this.$refs.canvas.getContext("2d");

            // Draw the current video frame onto the canvas
            ctx.drawImage(
                this.$refs.video,
                0,
                0,
                this.$refs.canvas.width,
                this.$refs.canvas.height
            );

            // Convert the canvas content to a data URL
            const dataURL = this.$refs.canvas.toDataURL("image/jpeg");

            this.$refs.video.pause();

            // Create a link to download the captured image
            this.sendImage(dataURL);
        },

        async sendImage(dataURL) {
            this.sendingImage = true;

            // Convert the dataURL to a Blob
            const byteString = atob(dataURL.split(",")[1]);
            const mimeString = dataURL
                .split(",")[0]
                .split(":")[1]
                .split(";")[0];
            const ab = new ArrayBuffer(byteString.length);
            const ia = new Uint8Array(ab);

            for (let i = 0; i < byteString.length; i++) {
                ia[i] = byteString.charCodeAt(i);
            }

            const blob = new Blob([ab], { type: mimeString });

            // Create a FormData object and append the Blob as a file
            const formData = new FormData();
            formData.append("type", "carte_de_visite");
            formData.append("file", blob, "image.jpg"); // 'file' is the field name, you can change it if needed

            // Send the FormData in a POST request using Axios
            let { data } = await ApiService.post(
                `project/${this.project.slug}/ocr`,
                formData
            );

            this.sendingImage = false;

            if (data) {
                // store.commit(ADD_PROSPECT, data);
                store.commit(CLOSE_MODAL);
                this.flashScanInfo();
            }
        },

        /**
         *
         */
        async addFiles(files) {
            this.uploadingFiles = [...this.uploadingFiles, ...files];
        },

        /**
         *
         */
        async dropFiles(e) {
            this.uploadingFiles = [
                ...this.uploadingFiles,
                ...e.dataTransfer.files,
            ];
        },

        /**
         *
         * @param {*} file
         */
        onFileUploaded(file) {
            this.uploadedFiles = [...this.uploadedFiles, file];
        },

        /**
         *
         */
        flashScanInfo() {
            flashInfo({
                title: this.$t("prospect.scan.success.title"),
                body: this.$t("prospect.scan.success.message"),
                duration: 5000,
            });
        },
    },

    watch: {
        tab(newValue) {
            if (newValue == 1) {
                this.startCamera();
            } else {
                this.stopCamera();
            }
        },

        uploadedFiles(newValue) {
            if (newValue.length == this.uploadingFiles.length) {
                this.flashScanInfo();
            }
        },
    },

    computed: {
        ...mapGetters(["project", "prospectFullName", "imports"]),

        ocrTypes() {
            return [
                {
                    name: "Carte de visite",
                    api: "mindee",
                    type: "carte_de_visite",
                    thumbnail: "/images/ocr/carte-de-visite.jpg",
                },
                ...this.imports
                    .filter((i) => i.source == "webservice")
                    .map((i) => ({
                        name: i.name,
                        api: "import",
                        type: i.id,
                        thumbnail: "/images/ocr/pdf.png",
                    })),
            ];
        },
    },
};
</script>
