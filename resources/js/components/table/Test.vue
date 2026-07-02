<template>
    <resizable-table
        :columns="columnsCount"
        :rows="rowsCount"
        :customWidths="widths"
        :fixed="fixed"
        @resize="resize"
        @move="move"
    >
        <!-- Header -->
        <template #header="{ column }">
            <span
                :class="[
                    'table-default-field-header',
                    column == 0 ? 'row-index' : '',
                ]"
                v-text="headers[column].name"
            ></span>
        </template>

        <!-- Body -->
        <template #cell="{ row, column, top }">
            <span
                v-if="headers[column].type == 'default'"
                :class="[
                    'table-default-field',
                    headers[column].index == 0 ? 'row-index' : '',
                ]"
                @click="
                    edit(
                        $event,
                        row < rows.length
                            ? rows[row][headers[column].index]
                            : '',
                        top
                    )
                "
                >{{
                    row < rows.length ? rows[row][headers[column].index] : ""
                }}</span
            >

            <span
                v-else-if="headers[column].type == 'tags'"
                class="table-tag-field"
            >
                <span v-if="row >= rows.length"></span>
                <span
                    v-else
                    v-for="tag in rows[row][headers[column].index]"
                    :key="tag.id"
                    v-text="tag.name"
                    :style="{ color: tag.color, backgroundColor: tag.bgcolor }"
                ></span>
            </span>
        </template>

        <!-- Extra -->
        <template #extra>
            <table-input
                :display="input.display"
                :left="input.left"
                :top="input.top"
                :width="input.width"
                :height="input.height"
                :value="input.value"
            />
            <table-selection
                :display="selection.display"
                :left="selection.left"
                :top="selection.top"
                :width="selection.width"
                :height="selection.height"
            />
        </template>
    </resizable-table>
</template>

<style>
.table-default-field-header {
    width: 100%;
    height: 26px;
    line-height: 26px;
    font-weight: normal;
    text-transform: uppercase;
    padding: 0 4px !important;
    text-align: center;
    display: inline-block;
    background-color: #f5f5f5;
}
/*.table-default-field-header.row-index {
    position: sticky;
    left: 0;
}*/

.table-default-field {
    width: 100%;
    height: 100%;
    padding: 0px 4px !important;
    display: block;
    /* vertical-align: top; */
    line-height: 21px;
    /*position: absolute;*/
    top: 0;
    left: 0;
    overflow: hidden;
    -webkit-user-select: none;
    -moz-user-select: none;
    user-select: none;
}
.table-default-field.row-index {
    text-align: center;
    background-color: #f5f5f5;
    font-size: 10px;
    z-index: 2;
}

.hc-virtual-scroller-table-body > table > thead > th:first-child {
    left: 0;
    z-index: 4;
}

.hc-virtual-scroller-table-body > table > tbody > tr > td:first-child:before {
    border: 1px solid #d5d5d5;
    bottom: -1px;
    content: "";
    display: inline-block;
    left: -1px;
    pointer-events: none;
    position: absolute;
    right: -1px;
    top: -1px;
}

.table-default-field:hover {
    /*outline: 2px solid #0b82e4;*/
    background-color: #fafafa;
    cursor: pointer;
}

.table-tag-field {
    width: 100%;
    height: 100%;
    display: inline-block;
    vertical-align: top;
    position: relative;
    overflow: hidden;
    user-select: none;
}
.table-tag-field > span {
    height: 23px;
    padding: 0 4px !important;
    display: inline-block;
    vertical-align: top;
    line-height: 23px;
    border-radius: 2px;
    font-size: 10px;
    margin: 0 1px 0 0;
}
</style>

<script>
/*
@mousedown="startSelect($event, row, column, top)"
@mouseenter="selecting($event, row, column, top)"
@mouseup="endSelect($event, row, column, top)"
*/
import VirtualTable from "./Table.vue";
import ResizableTable from "./ResizableTable.vue";
import TableInput from "./Input.vue";
import TableSelection from "./Selection.vue";

export default {
    components: {
        VirtualTable,
        ResizableTable,
        TableInput,
        TableSelection,
    },

    data() {
        return {
            columnsCount: 16,
            rowsCount: 10000,
            fixed: 2,

            input: {
                display: false,
                left: 0,
                top: 0,
                width: 0,
                height: 0,
                value: "",
            },

            selection: {
                display: false,
                selecting: false,
                startRow: 0,
                startColumn: 0,
                endRow: 0,
                endColumn: 0,
            },

            headers: [],
            rows: [],
        };
    },

    created() {
        this.headers = this.getHeaders();
        this.rows = this.getRows();
    },

    methods: {
        getHeaders() {
            const headers = [];

            headers.push({
                name: "I",
                type: "default",
                width: 50,
                index: 0,
            });
            for (let j = 1; j < this.columnsCount; ++j) {
                headers.push({
                    name: this.numberToLetters(j),
                    type: Math.random() < 0.1 ? "tags" : "default",
                    width: parseInt(Math.random() * 160) + 100,
                    index: j,
                });
            }

            return headers;
        },

        getRows() {
            const rows = [];

            for (let i = 0; i < this.rowsCount; ++i) {
                const row = [];
                row.push(i + 1);
                for (let j = 1; j < this.columnsCount; ++j) {
                    if (this.headers[j].type == "tags") {
                        const r = Math.floor(Math.random() * 2);
                        const tags = [];
                        for (let k = 0; k < r; ++k) {
                            const s = Math.floor(
                                Math.random() * this.tags.length
                            );
                            tags.push(this.tags[s]);
                        }
                        row.push(tags);
                    } else {
                        row.push(
                            (Math.random() * 123456789015 + "").substring(
                                0,
                                Math.random() * 10 + 5
                            )
                        );
                    }
                }
                rows.push(row);
            }

            return rows;
        },

        edit(e, cell, top) {
            const node = e.target.parentNode;

            this.input = {
                display: true,
                left: node.offsetLeft,
                top: node.offsetTop + top,
                width: node.offsetWidth,
                height: node.offsetHeight,
                value: cell,
            };
        },

        startSelect(e, row, column, t) {
            const node = e.target.parentNode;
            const nodeProperties = {
                left: node.offsetLeft,
                top: node.offsetTop,
                width: node.offsetWidth,
                height: node.offsetHeight,
            };

            this.selection = {
                display: true,
                selecting: true,
                node: nodeProperties,
                ...nodeProperties,
                top: node.offsetTop + t,
            };
        },

        selecting(e, row, column, t) {
            if (!this.selection.selecting) {
                return;
            }

            const node = e.target.parentNode;
            const left = Math.min(node.offsetLeft, this.selection.node.left);
            const top = Math.min(node.offsetTop, this.selection.node.top);
            const width =
                node.offsetLeft < this.selection.node.left
                    ? this.selection.node.left +
                      this.selection.node.width -
                      left
                    : node.offsetLeft + node.offsetWidth - left;
            const height =
                node.offsetTop < this.selection.node.top
                    ? this.selection.node.top + this.selection.node.height - top
                    : node.offsetTop + node.offsetHeight - top;

            this.selection = {
                ...this.selection,
                left: left,
                top: top + t,
                width: width,
                height: height,
            };
        },

        endSelect(e, row, column, t) {
            this.selection.selecting = false;
        },

        resize(column, width) {
            this.headers[column].width = width;
        },

        move(column, target) {
            if (target < this.fixed && column >= this.fixed) {
                this.fixed++;
            }

            if (target >= this.fixed && column < this.fixed) {
                this.fixed--;
            }

            if (column < target) {
                this.headers = [
                    ...this.headers.slice(0, column),
                    ...this.headers.slice(column + 1, target + 1),
                    this.headers[column],
                    ...this.headers.slice(target + 1),
                ];
            } else if (column > target) {
                this.headers = [
                    ...this.headers.slice(0, target),
                    this.headers[column],
                    ...this.headers.slice(target, column),
                    ...this.headers.slice(column + 1),
                ];
            }
        },

        numberToLetters(num) {
            const s = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            let letters = "";
            while (num >= 0) {
                letters = s[num % 26] + letters;
                num = Math.floor(num / 26) - 1;
            }
            return letters;
        },
    },

    computed: {
        tags() {
            return [
                {
                    id: 3829,
                    name: "Infinançable",
                    color: "#000000",
                    bgcolor: "#FFFFFF",
                },
                {
                    id: 718,
                    name: "Nouveau Lead",
                    color: "#1d1b1b",
                    bgcolor: "#b5d31d",
                },
                {
                    id: 726,
                    name: "RDV en cours",
                    color: "#ffffff",
                    bgcolor: "#3097d1",
                },
                {
                    id: 712,
                    name: "Envoyer devis",
                    color: "#ffffff",
                    bgcolor: "#FF8800",
                },
                {
                    id: 725,
                    name: "R2 à rappeler",
                    color: "#ffffff",
                    bgcolor: "#c05ee4",
                },
                {
                    id: 730,
                    name: "Signé",
                    color: "#ffffff",
                    bgcolor: "#c395d0",
                },
                {
                    id: 717,
                    name: "Négatif",
                    color: "#ffffff",
                    bgcolor: "#ef531f",
                },
                {
                    id: 2227,
                    name: "Panneau pub",
                    color: "#FFFFFF",
                    bgcolor: "#FF8800",
                },
                {
                    id: 719,
                    name: "NRP/ Répondeur 1",
                    color: "#FFFFFF",
                    bgcolor: "#8b6144",
                },
                {
                    id: 772,
                    name: "NRP / Répondeur 2",
                    color: "#FFFFFF",
                    bgcolor: "#8b6144",
                },
                {
                    id: 786,
                    name: "NRP / Répondeur 3",
                    color: "#ffffff",
                    bgcolor: "#8b6144",
                },
                {
                    id: 742,
                    name: "En cours de traitement EHE",
                    color: "#ffffff",
                    bgcolor: "#03b57c",
                },
                {
                    id: 1346,
                    name: "En cours de traitement Michael TH",
                    color: "#ffffff",
                    bgcolor: "#cf1b1b",
                },
                {
                    id: 766,
                    name: "En cours de traitement Alain",
                    color: "#ffffff",
                    bgcolor: "#2b12ff",
                },
                {
                    id: 1342,
                    name: "En attente MPR",
                    color: "#ffffff",
                    bgcolor: "#1fb56b",
                },
                {
                    id: 790,
                    name: "Nouveau Distributeur",
                    color: "#ffffff",
                    bgcolor: "#1d67c0",
                },
                {
                    id: 739,
                    name: "A rappeler",
                    color: "#ffffff",
                    bgcolor: "#1cfb30",
                },
                {
                    id: 741,
                    name: "NRP/ Répondeur",
                    color: "#ffffff",
                    bgcolor: "#76646e",
                },
                {
                    id: 813,
                    name: "SMS envoyé 1",
                    color: "#ffffff",
                    bgcolor: "#4f12d2",
                },
                {
                    id: 709,
                    name: "Attendre son appel",
                    color: "#ffffff",
                    bgcolor: "#3097d1",
                },
                {
                    id: 767,
                    name: "Devis envoyé",
                    color: "#ffffff",
                    bgcolor: "#5c9428",
                },
                {
                    id: 715,
                    name: "Installation en cours",
                    color: "#231f1f",
                    bgcolor: "#39ea4d",
                },
                {
                    id: 783,
                    name: "Faire facture",
                    color: "#ffffff",
                    bgcolor: "#b75e28",
                },
                {
                    id: 796,
                    name: "Facture emise",
                    color: "#ffffff",
                    bgcolor: "#f02028",
                },
                {
                    id: 807,
                    name: "Payé",
                    color: "#ffffff",
                    bgcolor: "#16fd08",
                },
                {
                    id: 1283,
                    name: "Maison témoin",
                    color: "#ffffff",
                    bgcolor: "#6b2579",
                },
                {
                    id: 714,
                    name: "Hors critère",
                    color: "#ffffff",
                    bgcolor: "#ec7474",
                },
                {
                    id: 713,
                    name: "Faux numéro",
                    color: "#ffffff",
                    bgcolor: "#3097d1",
                },
                {
                    id: 711,
                    name: "Contrat annulé",
                    color: "#ffffff",
                    bgcolor: "#ef531f",
                },
                {
                    id: 727,
                    name: "Refus de financement",
                    color: "#ffffff",
                    bgcolor: "#d12ecc",
                },
                {
                    id: 729,
                    name: "SAV",
                    color: "#FFFFFF",
                    bgcolor: "#d22d2d",
                },
                {
                    id: 740,
                    name: "Installé par un concurrent",
                    color: "#ffffff",
                    bgcolor: "#c76e18",
                },
            ];
        },

        widths() {
            return this.headers.map((header) => header.width);
        },
    },
};
</script>
