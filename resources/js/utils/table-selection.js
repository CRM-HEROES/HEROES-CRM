/**
 * Excel-like cell selection for the prospects table.
 *
 * - Drag the mouse over cells (or SHIFT + click) to select a range
 * - Click on the "select column" button of a column header to select a column
 * - CTRL/CMD + C copies the selection as tab separated values
 *   (can be pasted in Excel, Google Sheets...)
 * - CTRL/CMD + V pastes values (from the table, Excel, Sheets...)
 *   starting from the top-left cell of the selection.
 *   A single value is repeated on the whole selection.
 * - CTRL/CMD + X cuts, BACKSPACE / DELETE clears the selected cells
 * - CTRL/CMD + Z undoes, CTRL/CMD + Y (or CTRL + SHIFT + Z) redoes
 * - ESC clears the selection
 *
 * The mixin is meant to be used by Table.vue, it expects
 * `fixedColumns`, `notFixedColumns` and `prospects` to exist.
 */
import store from "@/store";
import { UPDATE_PROSPECT } from "@/actions/project/prospect";

const READ_ONLY_FIELDS = ["created_at", "updated_at"];
const CONFIRM_THRESHOLD = 50;
const HISTORY_LIMIT = 50;
const CHUNK_SIZE = 5;

/**
 * Serialize a value the way Excel does in TSV
 */
function toTsvValue(value) {
    value = value === null || value === undefined ? "" : String(value);

    if (/[\t\n\r"]/.test(value)) {
        return '"' + value.replace(/"/g, '""') + '"';
    }

    return value;
}

/**
 * Parse tab separated values (supports quoted values)
 */
function parseTsv(text) {
    const rows = [];
    let row = [];
    let value = "";
    let quoted = false;

    for (let i = 0; i < text.length; i++) {
        const char = text[i];

        if (quoted) {
            if (char === '"' && text[i + 1] === '"') {
                value += '"';
                i++;
            } else if (char === '"') {
                quoted = false;
            } else {
                value += char;
            }
        } else if (char === '"' && value === "") {
            quoted = true;
        } else if (char === "\t") {
            row.push(value);
            value = "";
        } else if (char === "\n" || char === "\r") {
            if (char === "\r" && text[i + 1] === "\n") {
                i++;
            }
            row.push(value);
            rows.push(row);
            row = [];
            value = "";
        } else {
            value += char;
        }
    }

    // Last value (Excel ends the text with a line break)
    if (value !== "" || row.length > 0) {
        row.push(value);
        rows.push(row);
    }

    return rows;
}

export default {
    data() {
        return {
            cellSelection: null,
            // Progress of the running operation { label, done, total }
            selectionProgress: null,
        };
    },

    mounted() {
        this._selPending = null;
        this._selDragging = false;
        this._selFrame = null;
        this._selRows = "";
        this._undoStack = [];
        this._redoStack = [];

        document.addEventListener("cut", this.onSelectionCut);
        document.addEventListener("mousedown", this.onSelectionMouseDown);
        document.addEventListener("mouseover", this.onSelectionMouseOver);
        document.addEventListener("mouseup", this.onSelectionMouseUp);
        document.addEventListener("copy", this.onSelectionCopy);
        document.addEventListener("paste", this.onSelectionPaste);
        document.addEventListener("keydown", this.onSelectionKeyDown);
        document.addEventListener("selectstart", this.onSelectionSelectStart);
    },

    beforeUnmount() {
        document.removeEventListener("cut", this.onSelectionCut);
        document.removeEventListener("mousedown", this.onSelectionMouseDown);
        document.removeEventListener("mouseover", this.onSelectionMouseOver);
        document.removeEventListener("mouseup", this.onSelectionMouseUp);
        document.removeEventListener("copy", this.onSelectionCopy);
        document.removeEventListener("paste", this.onSelectionPaste);
        document.removeEventListener("keydown", this.onSelectionKeyDown);
        document.removeEventListener(
            "selectstart",
            this.onSelectionSelectStart
        );
        document.body.classList.remove("hc-table-selecting");
    },

    updated() {
        // Vue may have re-rendered some cells
        if (this.cellSelection) {
            this.paintSelection();
        }
    },

    watch: {
        // Rows changed (page, filters...): the selection is not valid anymore
        // (a simple refresh of the same rows keeps it)
        prospects(rows) {
            const signature = rows.map((p) => p.id).join(",");

            if (signature !== this._selRows) {
                this._selRows = signature;
                this.clearSelection();
            }
        },
    },

    computed: {
        /**
         * Columns in the same order as displayed
         */
        selectionColumns() {
            return [...this.fixedColumns, ...this.notFixedColumns];
        },

        /**
         * Normalized rectangle of the selection
         */
        selectionRect() {
            const s = this.cellSelection;

            if (!s) {
                return null;
            }

            return {
                r1: Math.min(s.anchor.r, s.focus.r),
                r2: Math.max(s.anchor.r, s.focus.r),
                c1: Math.min(s.anchor.c, s.focus.c),
                c2: Math.max(s.anchor.c, s.focus.c),
            };
        },
    },

    methods: {
        /**
         * Find the cell position (row / column indexes) of an element
         */
        cellPosition(element) {
            const cell = element && element.closest
                ? element.closest("[data-col-key]")
                : null;

            if (!cell || !this.$el || !this.$el.contains(cell)) {
                return null;
            }

            const r = this.prospects.findIndex(
                (p) => String(p.id) === cell.dataset.pid
            );
            const c = this.selectionColumns.findIndex(
                (col) => col.key === cell.dataset.colKey
            );

            if (r < 0 || c < 0) {
                return null;
            }

            return { r, c };
        },

        clearSelection() {
            if (this.cellSelection) {
                this.cellSelection = null;
                this.paintSelection();
            }
        },

        onSelectionMouseDown(e) {
            if (e.button !== 0) {
                return;
            }

            const inside = this.$el && this.$el.contains(e.target);

            // Click on a header "select column" button
            if (inside && e.target.closest("[data-select-column]")) {
                return;
            }

            const pos = inside ? this.cellPosition(e.target) : null;

            if (!pos) {
                // Click outside the cells: leave the selection
                // unless the click is inside the table (headers...)
                if (!inside || !e.target.closest("thead, .hc-table-header")) {
                    this.clearSelection();
                }
                this._selPending = null;
                return;
            }

            // SHIFT + click extends the selection
            if (e.shiftKey && this.cellSelection) {
                e.preventDefault();
                if (document.activeElement) {
                    document.activeElement.blur();
                }
                this.cellSelection = {
                    anchor: this.cellSelection.anchor,
                    focus: pos,
                };
                this.paintSelection();
                return;
            }

            this.clearSelection();
            this._selPending = pos;
            this._selDragging = false;
        },

        onSelectionMouseOver(e) {
            if (!this._selPending) {
                return;
            }

            const pos = this.cellPosition(e.target);

            if (!pos) {
                return;
            }

            const origin = this._selPending;

            if (
                !this._selDragging &&
                pos.r === origin.r &&
                pos.c === origin.c
            ) {
                return;
            }

            // Start dragging: we stop editing the input
            if (!this._selDragging) {
                this._selDragging = true;
                document.body.classList.add("hc-table-selecting");

                if (document.activeElement) {
                    document.activeElement.blur();
                }
                window.getSelection && window.getSelection().removeAllRanges();
            }

            this.cellSelection = { anchor: origin, focus: pos };
            this.paintSelection();
        },

        onSelectionMouseUp() {
            this._selPending = null;
            this._selDragging = false;
            document.body.classList.remove("hc-table-selecting");
        },

        onSelectionSelectStart(e) {
            if (this._selDragging) {
                e.preventDefault();
            }
        },

        /**
         * Is the user typing in a field ?
         */
        isEditingField() {
            const el = document.activeElement;

            if (!el || el === document.body) {
                return false;
            }

            if (
                el.isContentEditable ||
                el.tagName === "TEXTAREA" ||
                el.tagName === "SELECT"
            ) {
                return true;
            }

            return (
                el.tagName === "INPUT" &&
                !["checkbox", "radio", "button", "submit"].includes(el.type)
            );
        },

        onSelectionKeyDown(e) {
            if (e.key === "Escape" && this.cellSelection) {
                this.clearSelection();
                return;
            }

            if (this.isEditingField()) {
                return;
            }

            // Clear cells
            if (
                (e.key === "Backspace" || e.key === "Delete") &&
                this.cellSelection
            ) {
                e.preventDefault();
                this.clearSelectedCells("Suppression");
                return;
            }

            const mod = e.ctrlKey || e.metaKey;
            const key = e.key.toLowerCase();

            if (mod && key === "z" && !e.shiftKey && this._undoStack.length) {
                e.preventDefault();
                this.undo();
            } else if (
                mod &&
                (key === "y" || (key === "z" && e.shiftKey)) &&
                this._redoStack.length
            ) {
                e.preventDefault();
                this.redo();
            }
        },

        /**
         * Select a whole column (all loaded rows)
         * SHIFT extends from the previously selected column
         */
        selectColumn(key, extend) {
            const c = this.selectionColumns.findIndex((col) => col.key === key);

            if (c < 0 || this.prospects.length === 0) {
                return;
            }

            const last = this.prospects.length - 1;
            const anchorC =
                extend && this.cellSelection ? this.cellSelection.anchor.c : c;

            if (document.activeElement) {
                document.activeElement.blur();
            }

            this.cellSelection = {
                anchor: { r: 0, c: anchorC },
                focus: { r: last, c },
            };
            this.paintSelection();
        },

        onSelectColumnEvent(e) {
            this.selectColumn(e.detail.key, e.detail.extend);
        },

        /**
         * Apply CSS classes on the selected cells
         */
        paintSelection() {
            if (this._selFrame) {
                return;
            }

            this._selFrame = requestAnimationFrame(() => {
                this._selFrame = null;

                if (!this.$el || !this.$el.querySelectorAll) {
                    return;
                }

                const rect = this.selectionRect;
                const rows = new Map(this.prospects.map((p, i) => [String(p.id), i]));
                const cols = new Map(
                    this.selectionColumns.map((col, i) => [col.key, i])
                );

                this.$el.querySelectorAll("[data-col-key]").forEach((cell) => {
                    const r = rows.get(cell.dataset.pid);
                    const c = cols.get(cell.dataset.colKey);
                    const selected =
                        rect &&
                        r >= rect.r1 &&
                        r <= rect.r2 &&
                        c >= rect.c1 &&
                        c <= rect.c2;

                    cell.classList.toggle("hc-cell-sel", !!selected);
                    cell.classList.toggle("hc-cell-sel-t", selected && r === rect.r1);
                    cell.classList.toggle("hc-cell-sel-b", selected && r === rect.r2);
                    cell.classList.toggle("hc-cell-sel-l", selected && c === rect.c1);
                    cell.classList.toggle("hc-cell-sel-r", selected && c === rect.c2);
                });
            });
        },

        /**
         * Text value of a cell
         */
        selectionCellValue(prospect, column) {
            if (column.category === "default") {
                return prospect[column.key];
            }

            if (column.category === "meta") {
                return prospect.meta ? prospect.meta[column.id] : "";
            }

            // Other kind of cells (labels, users, history...)
            // we copy what is displayed
            const cell = this.$el.querySelector(
                `#prospect-${prospect.id} [data-col-key="${column.key}"]`
            );

            return cell ? cell.textContent.replace(/\s+/g, " ").trim() : "";
        },

        /**
         * Selection as tab separated values
         */
        selectionToTsv() {
            const rect = this.selectionRect;
            const lines = [];

            for (let r = rect.r1; r <= rect.r2; r++) {
                const values = [];

                for (let c = rect.c1; c <= rect.c2; c++) {
                    values.push(
                        toTsvValue(
                            this.selectionCellValue(
                                this.prospects[r],
                                this.selectionColumns[c]
                            )
                        )
                    );
                }

                lines.push(values.join("\t"));
            }

            return lines.join("\n");
        },

        onSelectionCopy(e) {
            if (
                !this.selectionRect ||
                !e.clipboardData ||
                this.isEditingField()
            ) {
                return;
            }

            e.clipboardData.setData("text/plain", this.selectionToTsv());
            e.preventDefault();

            const rect = this.selectionRect;
            const count = (rect.r2 - rect.r1 + 1) * (rect.c2 - rect.c1 + 1);
            flashInfo({ title: "Copié", body: `${count} cellule(s) copiée(s)` });
        },

        onSelectionCut(e) {
            if (
                !this.selectionRect ||
                !e.clipboardData ||
                this.isEditingField()
            ) {
                return;
            }

            e.clipboardData.setData("text/plain", this.selectionToTsv());
            e.preventDefault();

            this.clearSelectedCells("Coupe");
        },

        onSelectionPaste(e) {
            if (!e.clipboardData || !this.$el) {
                return;
            }

            const editing = this.isEditingField();
            const text = e.clipboardData.getData("text/plain");

            if (!text) {
                return;
            }

            const rect = editing ? null : this.selectionRect;
            let start;

            if (rect) {
                start = { r: rect.r1, c: rect.c1 };
            } else {
                // No selection: only handle multi-cells content pasted
                // in a cell, a simple text is pasted normally in the input
                const pos = this.cellPosition(e.target);

                if (!pos || !/[\t\n\r]/.test(text.replace(/[\r\n]+$/, ""))) {
                    return;
                }

                start = pos;
            }

            const grid = parseTsv(text);

            if (grid.length === 0) {
                return;
            }

            e.preventDefault();

            // Build list of target cells
            const targets = [];
            const single = grid.length === 1 && grid[0].length === 1;

            if (single && rect) {
                for (let r = rect.r1; r <= rect.r2; r++) {
                    for (let c = rect.c1; c <= rect.c2; c++) {
                        targets.push({ r, c, value: grid[0][0] });
                    }
                }
            } else {
                grid.forEach((line, i) => {
                    line.forEach((value, j) => {
                        targets.push({ r: start.r + i, c: start.c + j, value });
                    });
                });
            }

            this.changeCells(targets, "Collage");
        },

        /**
         * Empty all the selected cells
         */
        clearSelectedCells(label) {
            const rect = this.selectionRect;

            if (!rect) {
                return;
            }

            const targets = [];

            for (let r = rect.r1; r <= rect.r2; r++) {
                for (let c = rect.c1; c <= rect.c2; c++) {
                    targets.push({ r, c, value: "" });
                }
            }

            this.changeCells(targets, label);
        },

        /**
         * Convert targets ({ r, c, value }) to changes
         * then send them
         */
        changeCells(targets, label) {
            const changes = [];
            let ignored = 0;

            targets.forEach(({ r, c, value }) => {
                const prospect = this.prospects[r];
                const column = this.selectionColumns[c];

                if (!prospect || !column) {
                    return; // outside the table
                }

                const editable =
                    (column.category === "default" ||
                        column.category === "meta") &&
                    !READ_ONLY_FIELDS.includes(column.key) &&
                    prospect.id > 0 &&
                    !store.getters.prospectReadOnly &&
                    !prospect.deleted_at &&
                    !prospect.processed_at;

                if (!editable) {
                    ignored++;
                    return;
                }

                const before = this.selectionCellValue(prospect, column) ?? "";

                if (before === value) {
                    return;
                }

                changes.push({
                    pid: prospect.id,
                    meta: column.category === "meta",
                    key: column.category === "meta" ? column.id : column.key,
                    before,
                    after: value,
                });
            });

            if (changes.length === 0) {
                if (ignored > 0) {
                    flashWarning({
                        title: label,
                        body: "Ces cellules ne sont pas modifiables.",
                    });
                }
                return;
            }

            const run = () => this.executeChanges(changes, label, "do", ignored);

            if (changes.length >= CONFIRM_THRESHOLD) {
                hcConfirm(
                    `Voulez-vous vraiment modifier ${changes.length} cellule(s) ? (Ctrl+Z pour annuler)`,
                    run
                );
            } else {
                run();
            }
        },

        undo() {
            const operation = this._undoStack.pop();

            if (operation) {
                this.executeChanges(
                    operation.changes,
                    "Annulation",
                    "undo",
                    0,
                    operation
                );
            }
        },

        redo() {
            const operation = this._redoStack.pop();

            if (operation) {
                this.executeChanges(
                    operation.changes,
                    "Rétablissement",
                    "redo",
                    0,
                    operation
                );
            }
        },

        /**
         * Send changes to the API, prospect by prospect,
         * while showing the progress
         *
         * @param {Array} changes
         * @param {String} label
         * @param {String} mode do | undo | redo
         * @param {Number} ignored Number of ignored cells
         * @param {Object} operation Operation moved between undo/redo stacks
         */
        async executeChanges(
            changes,
            label,
            mode,
            ignored = 0,
            operation = null
        ) {
            if (this.selectionProgress) {
                flashWarning({
                    title: label,
                    body: "Une opération est déjà en cours, patientez un instant.",
                });

                // Put the operation back
                if (mode === "undo") this._undoStack.push(operation);
                if (mode === "redo") this._redoStack.push(operation);
                return;
            }

            // One update per prospect
            const updates = new Map();

            changes.forEach((change) => {
                const value = mode === "undo" ? change.before : change.after;
                const update = updates.get(change.pid) || { id: change.pid };

                if (change.meta) {
                    update.meta = { ...(update.meta || {}), [change.key]: value };
                } else {
                    update[change.key] = value;
                }

                updates.set(change.pid, update);
            });

            const list = [...updates.values()];
            this.selectionProgress = { label, done: 0, total: list.length };

            let failed = 0;

            try {
                // Chunks of requests in parallel
                for (let i = 0; i < list.length; i += CHUNK_SIZE) {
                    const results = await Promise.allSettled(
                        list.slice(i, i + CHUNK_SIZE).map((update) =>
                            store.dispatch(UPDATE_PROSPECT, update).finally(() => {
                                if (this.selectionProgress) {
                                    this.selectionProgress.done++;
                                }
                            })
                        )
                    );

                    failed += results.filter((r) => r.status === "rejected").length;
                }
            } finally {
                this.selectionProgress = null;
            }

            // History
            const entry = operation || { changes };

            if (mode === "undo") {
                this._redoStack.push(entry);
            } else {
                this._undoStack.push(entry);

                if (mode === "do") {
                    this._redoStack = [];
                }
            }

            if (this._undoStack.length > HISTORY_LIMIT) {
                this._undoStack.shift();
            }

            if (failed > 0) {
                flashError({
                    title: label,
                    body: `${failed} prospect(s) n'ont pas pu être modifiés.`,
                });
            } else {
                flashInfo({
                    title: label,
                    body:
                        `${changes.length} cellule(s) modifiée(s)` +
                        (ignored > 0 ? `, ${ignored} ignorée(s)` : ""),
                });
            }
        },
    },
};
