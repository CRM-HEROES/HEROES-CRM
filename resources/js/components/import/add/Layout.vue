<template>
    <form
        class="hc-flex-column"
        style="height: 100%"
        @submit.prevent="storeImport"
    >
        <item-list gap="5px">
            <!--v-field :label="$t('description')" v-slot="p">
                    <textarea
                        v-model="prospectImport.description"
                        :placeholder="p.label + ' ...'"
                    ></textarea>
                </v-field-->
            <v-field :label="$t('import.add.from.label')" required>
                <select v-model="prospectImport.source">
                    <option
                        value="file"
                        v-text="$t('import.add.from.local_file')"
                    ></option>
                    <option
                        value="google_sheets"
                        v-text="$t('import.add.from.google_sheets')"
                    ></option>
                </select>
            </v-field>

            <v-field
                v-if="prospectImport.source == 'file'"
                :label="$t('import.add.file')"
                required
                v-slot="{ label }"
                ><input
                    required
                    ref="fileInput"
                    type="file"
                    accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                    @change="setFile"
            /></v-field>

            <v-field
                v-if="prospectImport.source == 'google_sheets'"
                :label="$t('import.add.google_sheets.url')"
                required
                v-slot="{ label }"
                ><input
                    required
                    type="url"
                    :placeholder="label + ' ...'"
                    v-model="prospectImport.source_url"
            /></v-field>

            <v-field
                v-if="prospectImport.source == 'google_sheets'"
                :label="$t('import.add.google_sheets.sync_enabled')"
            >
                <input
                    type="checkbox"
                    style="width: auto; min-height: 0"
                    v-model="prospectImport.sync_enabled"
                />
            </v-field>

            <v-field
                v-if="
                    prospectImport.source == 'google_sheets' &&
                    prospectImport.sync_enabled
                "
                :label="$t('import.add.google_sheets.sync_interval')"
            >
                <select v-model.number="prospectImport.sync_interval_minutes">
                    <option
                        :value="5"
                        v-text="$t('import.add.google_sheets.sync_interval_5')"
                    ></option>
                    <option
                        :value="30"
                        v-text="$t('import.add.google_sheets.sync_interval_30')"
                    ></option>
                    <option
                        :value="60"
                        v-text="$t('import.add.google_sheets.sync_interval_60')"
                    ></option>
                    <option
                        :value="180"
                        v-text="$t('import.add.google_sheets.sync_interval_180')"
                    ></option>
                    <option
                        :value="1440"
                        v-text="$t('import.add.google_sheets.sync_interval_1440')"
                    ></option>
                </select>
            </v-field>

            <v-field :label="$t('import.add.name')" required v-slot="{ label }"
                ><input
                    required
                    :placeholder="label + ' ...'"
                    v-model="prospectImport.name"
            /></v-field>

            <template v-if="isCsvFile">
                <v-field :label="$t('import.add.field_delimiter')" required>
                    <select v-model="prospectImport.field_delimiter">
                        <option value=""></option>
                        <option
                            value=","
                            v-text="$t('import.add.comma')"
                        ></option>
                        <option
                            value=";"
                            v-text="$t('import.add.semicolon')"
                        ></option>
                        <option
                            value="tab"
                            v-text="$t('import.add.tab')"
                        ></option>
                        <option
                            value=" "
                            v-text="$t('import.add.space')"
                        ></option>
                    </select>
                </v-field>

                <v-field :label="$t('import.add.field_enclosure')" required>
                    <select v-model="prospectImport.field_enclosure">
                        <option value=""></option>
                        <option value='"'>&quot;</option>
                        <option value="'">'</option>
                    </select>
                </v-field>
            </template>
        </item-list>
        <buttons>
            <button v-text="$t('add')"></button>
        </buttons>
        <loading :loading="addingImport" />
    </form>
</template>

<script>
import store from "@/store";

// Actions
import { ADD_IMPORT, SET_IMPORT, SHOW_IMPORT } from "@/actions/project/import";
import { CLOSE_MODAL } from "@/actions/modal";
import { OPEN_SLIDE } from "@/actions/slide";

export default {
    data() {
        return {
            prospectImport: this.newImport(),
            addingImport: false,
        };
    },

    methods: {
        /**
         *
         */
        newImport() {
            return {
                name: "",
                description: "",
                source: "file",
                file: null,
                source_url: "",
                sync_enabled: false,
                sync_interval_minutes: 30,
                field_delimiter: ",",
                field_enclosure: "",
            };
        },

        /**
         *
         */
        async storeImport() {
            this.addingImport = true;

            var formData = new FormData();
            for (const i in this.prospectImport) {
                let value = this.prospectImport[i];
                // FormData stringifies booleans as the literal "true"/"false",
                // which Laravel's "boolean" validation rule rejects (it only
                // accepts true/false/0/1/"0"/"1"). Send 1/0 instead.
                if (typeof value === "boolean") {
                    value = value ? 1 : 0;
                }
                formData.append(i, value);
            }

            let succeeded = false;

            try {
                const pi = await store.dispatch(ADD_IMPORT, formData);

                if (pi.already_imported_url) {
                    flashWarning({
                        title: this.$t("import.add.title"),
                        body: "Cette URL a déjà été importée précédemment.",
                    });
                }

                const prospectImport = await store.dispatch(SHOW_IMPORT, pi.id);
                store.commit(OPEN_SLIDE, "import");
                store.commit(SET_IMPORT, prospectImport);
                succeeded = true;
            } catch (e) {
                const errors = e.response && e.response.data && e.response.data.errors;
                const message =
                    (errors && Object.values(errors)[0] && Object.values(errors)[0][0]) ||
                    (e.response && e.response.data && e.response.data.message) ||
                    e.message;

                flashError({
                    title: this.$t("import.add.title"),
                    body: message,
                });
            } finally {
                this.addingImport = false;

                // En cas d'erreur, on laisse le formulaire tel quel
                // (modal ouvert, valeurs conservées) pour que l'utilisateur
                // puisse corriger l'URL/le nom et réessayer.
                if (succeeded) {
                    // Réinitialise complètement le formulaire (modal)
                    this.prospectImport = this.newImport();
                    // Vide aussi l'input fichier natif (le nom du fichier reste
                    // affiché sinon, car un input type=file ne se réinitialise pas
                    // en changeant la variable liée)
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = "";
                    }
                    // Ferme le modal
                    store.commit(CLOSE_MODAL);
                }
            }
        },

        /**
         *
         * @param {*} e
         */
        setFile(e) {
            this.prospectImport.file = e.target.files[0];

            if (this.isCsvFile) {
                this.setCsvDelimiterEnclosure();
            }

            if (this.prospectImport.name == "") {
                this.prospectImport.name =
                    this.prospectImport.file.name.substring(
                        0,
                        this.prospectImport.file.name.lastIndexOf(".")
                    );
            }
        },

        /**
         * Automatically define
         * field delimiter
         * and field enclosure
         */
        setCsvDelimiterEnclosure() {
            const reader = new FileReader();

            reader.onload = (e) => {
                const text = reader.result;
                const firstLine = text.split("\n").shift();
                const { delimiter, enclosure } =
                    this.getCSVDelimiterEnclosure(firstLine);

                this.prospectImport.field_delimiter = delimiter;
                this.prospectImport.field_enclosure = enclosure;
            };

            reader.readAsText(this.prospectImport.file, "UTF-8");
        },

        /**
         * Check field delimiter and enclosure from first line
         * @param {*} line
         */
        getCSVDelimiterEnclosure(line) {
            // Detect the delimiter by counting occurrences of common delimiter characters
            const delimiterCounts = { ",": 0, ";": 0, "\t": 0 };
            for (let i = 0; i < line.length; i++) {
                if (line[i] === ",") delimiterCounts[","]++;
                else if (line[i] === ";") delimiterCounts[";"]++;
                else if (line[i] === "\t") delimiterCounts["\t"]++;
            }

            // Get the delimiter with the highest count
            const delimiter = Object.keys(delimiterCounts).reduce((a, b) =>
                delimiterCounts[a] > delimiterCounts[b] ? a : b
            );

            // Detect the enclosure by finding the first occurrence of a character inside the field
            const enclosure = line.match(/[^,;\t][^"'\n\r]*(["'])[^"'\n\r]*\1/)
                ? line.match(/[^,;\t][^"'\n\r]*(["'])[^"'\n\r]*\1/)[1]
                : "";

            return {
                delimiter,
                enclosure,
            };
        },
    },

    computed: {
        /**
         * If import file is CSV
         * (Show field delimiter and enclosure param fields)
         */
        isCsvFile() {
            return (
                this.prospectImport.file &&
                this.prospectImport.file.name.substring(
                    this.prospectImport.file.name.lastIndexOf(".")
                ) == ".csv"
            );
        },
    },
};
</script>
