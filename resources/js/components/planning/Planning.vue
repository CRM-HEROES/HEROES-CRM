<template>
    <div class="hc-planning">
        <!-- Toolbar -->
        <div class="hc-planning-toolbar">
            <div class="hc-planning-week">
                <a
                    class="fa fa-caret-left hc-planning-nav"
                    @click.prevent="previousWeek"
                    title="Semaine précédente"
                ></a>
                <span v-text="weekLabel"></span>
                <a
                    class="fa fa-caret-right hc-planning-nav"
                    @click.prevent="nextWeek"
                    title="Semaine suivante"
                ></a>
            </div>
            <div class="hc-planning-legend">
                <span><i class="dot present"></i> Présent</span>
                <span><i class="dot retard"></i> Retard</span>
                <span><i class="dot conge"></i> Congé</span>
                <span><i class="dot pause"></i> Pause</span>
                <span><i class="dot absent"></i> Absent</span>
            </div>
        </div>

        <!-- Grid -->
        <div class="hc-planning-table-wrapper">
            <table class="hc-planning-table">
                <thead>
                    <tr>
                        <th class="hc-planning-emp">Employé</th>
                        <th v-for="day in days" :key="day.date" v-text="day.label"></th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="employee in employees" :key="employee.id">
                        <td class="hc-planning-emp">
                            <div class="hc-planning-emp-name" v-text="employee.name"></div>
                            <div class="hc-planning-emp-sub" v-text="employee.email"></div>
                        </td>
                        <td
                            v-for="day in days"
                            :key="day.date"
                            class="hc-planning-cell"
                            @click="openEditor(employee, day.date)"
                        >
                            <template v-if="cellStatus(employee.id, day.date)">
                                <div
                                    v-if="cellTimes(employee.id, day.date)"
                                    class="hc-planning-times"
                                    v-text="cellTimes(employee.id, day.date)"
                                ></div>
                                <span
                                    class="hc-planning-pill"
                                    :class="cellStatus(employee.id, day.date).cls"
                                    v-text="cellStatus(employee.id, day.date).label"
                                ></span>
                            </template>
                            <span v-else class="hc-planning-empty">+</span>
                        </td>
                        <td
                            class="hc-planning-total"
                            v-text="employeeTotal(employee.id)"
                        ></td>
                    </tr>
                    <tr v-if="!loading && employees.length == 0">
                        <td :colspan="days.length + 2" class="hc-planning-none">
                            Aucun employé dans ce projet.
                        </td>
                    </tr>
                </tbody>
            </table>
            <loading :loading="loading" />
        </div>

        <!-- Editor modal -->
        <div v-if="editing" class="hc-planning-overlay" @click.self="closeEditor">
            <div class="hc-planning-editor">
                <div class="hc-planning-editor-header">
                    <div>
                        <div class="hc-planning-editor-title" v-text="editing.employee.name"></div>
                        <div class="hc-planning-emp-sub" v-text="editing.dateLabel"></div>
                    </div>
                    <a class="fa fa-close hc-planning-nav" @click.prevent="closeEditor"></a>
                </div>

                <div class="hc-planning-field">
                    <label>Statut</label>
                    <select v-model="form.status">
                        <option value="present">Présent</option>
                        <option value="leave">Congé</option>
                        <option value="pause">Pause</option>
                        <option value="absent">Absent</option>
                    </select>
                </div>

                <template v-if="form.status == 'leave'">
                    <div class="hc-planning-field-row">
                        <div class="hc-planning-field">
                            <label>Du</label>
                            <input type="date" v-model="form.leave_from" />
                        </div>
                        <div class="hc-planning-field">
                            <label>Au</label>
                            <input type="date" v-model="form.leave_to" />
                        </div>
                    </div>
                    <div class="hc-planning-preview">
                        Le congé sera appliqué à tous les jours de la période.
                    </div>
                </template>

                <template v-if="form.status == 'present'">
                    <div class="hc-planning-field-row">
                        <div class="hc-planning-field">
                            <label>Arrivée prévue</label>
                            <input type="time" v-model="form.expected_arrival" />
                        </div>
                        <div class="hc-planning-field">
                            <label>Départ prévu</label>
                            <input type="time" v-model="form.expected_departure" />
                        </div>
                    </div>
                    <div class="hc-planning-field-row">
                        <div class="hc-planning-field">
                            <label>Arrivée réelle</label>
                            <input type="time" v-model="form.arrival" />
                        </div>
                        <div class="hc-planning-field">
                            <label>Départ réel</label>
                            <input type="time" v-model="form.departure" />
                        </div>
                    </div>
                    <div class="hc-planning-field">
                        <label>Pause (h:min)</label>
                        <input type="time" v-model="breakTime" />
                    </div>
                    <label
                        style="display:flex;align-items:center;gap:8px;font-size:12px;color:#555;margin-bottom:10px;cursor:pointer;"
                    >
                        <input
                            type="checkbox"
                            v-model="form.save_as_default"
                            style="width:auto;"
                        />
                        Enregistrer ces horaires comme habituels pour cet employé
                    </label>
                    <div v-if="livePreview" class="hc-planning-preview" v-html="livePreview"></div>
                </template>

                <div class="hc-planning-field">
                    <label>Note</label>
                    <textarea v-model="form.note" rows="2" placeholder="Note ..."></textarea>
                </div>

                <div class="hc-planning-editor-buttons">
                    <button
                        v-if="editing.id"
                        class="hc-planning-btn danger"
                        @click.prevent="remove"
                        v-text="
                            form.status == 'leave'
                                ? 'Annuler le congé'
                                : 'Supprimer'
                        "
                    ></button>
                    <button class="hc-planning-btn primary" @click.prevent="save">
                        Enregistrer
                    </button>
                    <loading :loading="saving" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import attendanceService from "@/apis/project/attendance";

const DAY_LABELS = ["Lun", "Mar", "Mer", "Jeu", "Ven"];
const MONTHS = [
    "janvier", "février", "mars", "avril", "mai", "juin",
    "juillet", "août", "septembre", "octobre", "novembre", "décembre",
];

// Horaires habituels par défaut (fallback si rien en localStorage)
const DEFAULT_SCHEDULE = {
    expected_arrival: "08:30",
    expected_departure: "17:00",
    break_minutes: 0,
};

export default {
    data() {
        return {
            weekStart: this.getMonday(new Date()),
            employees: [],
            attendances: [],
            loading: false,
            saving: false,
            editing: null,
            form: this.emptyForm(),
        };
    },

    created() {
        this.fetchEmployees();
        this.fetchAttendances();
    },

    methods: {
        // --- Date helpers ---

        getMonday(d) {
            const date = new Date(d);
            const offset = (date.getDay() + 6) % 7;
            date.setDate(date.getDate() - offset);
            date.setHours(0, 0, 0, 0);
            return date;
        },

        toYMD(date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, "0");
            const d = String(date.getDate()).padStart(2, "0");
            return `${y}-${m}-${d}`;
        },

        addDays(date, n) {
            const d = new Date(date);
            d.setDate(d.getDate() + n);
            return d;
        },

        // Extrait "HH:mm" d'une valeur date/heure (insensible au fuseau)
        toTime(value) {
            if (!value) return "";
            const m = String(value).match(/[T ](\d{2}:\d{2})/);
            if (m) return m[1];
            const m2 = String(value).match(/^(\d{2}:\d{2})/);
            return m2 ? m2[1] : "";
        },

        combine(date, time) {
            return time ? `${date} ${time}:00` : null;
        },

        emptyForm() {
            return {
                status: "present",
                expected_arrival: DEFAULT_SCHEDULE.expected_arrival,
                expected_departure: DEFAULT_SCHEDULE.expected_departure,
                arrival: "",
                departure: "",
                break_minutes: DEFAULT_SCHEDULE.break_minutes,
                note: "",
                leave_from: "",
                leave_to: "",
                save_as_default: false,
            };
        },

        // --- Horaires habituels par employé (localStorage) ---

        defaultsStorageKey() {
            return (
                "hc-planning-defaults-" +
                (this.project ? this.project.slug : "")
            );
        },

        getEmployeeDefault(employeeId) {
            try {
                const all = JSON.parse(
                    localStorage.getItem(this.defaultsStorageKey()) || "{}"
                );
                return { ...DEFAULT_SCHEDULE, ...(all[employeeId] || {}) };
            } catch (e) {
                return { ...DEFAULT_SCHEDULE };
            }
        },

        saveEmployeeDefault(employeeId, values) {
            let all = {};
            try {
                all = JSON.parse(
                    localStorage.getItem(this.defaultsStorageKey()) || "{}"
                );
            } catch (e) {
                all = {};
            }
            all[employeeId] = {
                expected_arrival: values.expected_arrival,
                expected_departure: values.expected_departure,
                break_minutes: values.break_minutes || 0,
            };
            localStorage.setItem(
                this.defaultsStorageKey(),
                JSON.stringify(all)
            );
        },

        // --- Data ---
        async fetchEmployees() {
            // Tous les utilisateurs de la base (endpoint dédié au planning)
            const { data } = await attendanceService.users(this.project.slug);
            this.employees = Array.isArray(data) ? data : data.data || [];
        },

        async fetchAttendances() {
            this.loading = true;
            try {
                const { data } = await attendanceService.get(
                    this.project.slug,
                    this.toYMD(this.weekStart),
                    this.toYMD(this.addDays(this.weekStart, 6))
                );
                this.attendances = data;
            } finally {
                this.loading = false;
            }
        },

        previousWeek() {
            this.weekStart = this.addDays(this.weekStart, -7);
            this.fetchAttendances();
        },

        nextWeek() {
            this.weekStart = this.addDays(this.weekStart, 7);
            this.fetchAttendances();
        },

        // --- Cell rendering ---

        getAttendance(userId, date) {
            return this.attendances.find(
                (a) => a.user_id == userId && this.toYMD(new Date(a.date)) == date
            );
        },

        cellStatus(userId, date) {
            const a = this.getAttendance(userId, date);
            if (!a) return null;

            if (a.status == "leave") return { label: "Congé", cls: "conge" };
            if (a.status == "pause") return { label: "Pause", cls: "pause" };
            if (a.status == "absent") return { label: "Absent", cls: "absent" };

            if (a.arrival_at) {
                if (a.retard_minutes > 0) {
                    return { label: "Retard +" + a.retard_minutes + "′", cls: "retard" };
                }
                return { label: "Présent", cls: "present" };
            }

            return { label: "—", cls: "" };
        },

        cellTimes(userId, date) {
            const a = this.getAttendance(userId, date);
            if (!a || !a.arrival_at) return "";
            return this.toTime(a.arrival_at) + "–" + this.toTime(a.departure_at);
        },

        employeeTotal(userId) {
            let minutes = 0;
            let retards = 0;
            this.days.forEach((day) => {
                const a = this.getAttendance(userId, day.date);
                if (a) {
                    minutes += a.worked_minutes || 0;
                    if (a.retard_minutes > 0) retards++;
                }
            });
            const h = Math.floor(minutes / 60);
            const m = minutes % 60;
            let out = h + "h" + (m > 0 ? String(m).padStart(2, "0") : "");
            if (retards > 0) out += " · " + retards + " ret.";
            return out;
        },

        // --- Editor ---

        openEditor(employee, date) {
            const a = this.getAttendance(employee.id, date);
            const d = new Date(date);
            this.editing = {
                id: a ? a.id : null,
                employee,
                date,
                dateLabel:
                    DAY_LABELS[(d.getDay() + 6) % 7] +
                    " " +
                    d.getDate() +
                    " " +
                    MONTHS[d.getMonth()],
            };

            const def = this.getEmployeeDefault(employee.id);

            if (a) {
                this.form = {
                    status: a.status || "present",
                    expected_arrival:
                        this.toTime(a.expected_arrival) || def.expected_arrival,
                    expected_departure:
                        this.toTime(a.expected_departure) ||
                        def.expected_departure,
                    arrival: this.toTime(a.arrival_at),
                    departure: this.toTime(a.departure_at),
                    break_minutes:
                        a.break_minutes != null
                            ? a.break_minutes
                            : def.break_minutes,
                    note: a.note || "",
                    leave_from: "",
                    leave_to: "",
                    save_as_default: false,
                };
            } else {
                // Nouveau pointage → pré-rempli avec les horaires habituels
                this.form = this.emptyForm();
                this.form.expected_arrival = def.expected_arrival;
                this.form.expected_departure = def.expected_departure;
                this.form.break_minutes = def.break_minutes;
            }

            // Congé : on pré-remplit Du/Au avec la période contiguë détectée
            // (pour pouvoir annuler/modifier tout le congé d'un coup).
            if (a && a.status == "leave") {
                const block = this.leaveBlock(employee.id, date);
                this.form.leave_from = block.from;
                this.form.leave_to = block.to;
                // Mémorise la période d'origine (pour gérer la réduction)
                this.editing.leaveOriginal = { from: block.from, to: block.to };
            } else {
                this.form.leave_from = date;
                this.form.leave_to = date;
                this.editing.leaveOriginal = null;
            }
        },

        /**
         * Détecte la période de congé contiguë autour d'un jour donné.
         */
        leaveBlock(userId, date) {
            const isLeave = (ymd) => {
                const a = this.getAttendance(userId, ymd);
                return a && a.status == "leave";
            };

            let from = new Date(date);
            let to = new Date(date);

            while (isLeave(this.toYMD(this.addDays(from, -1)))) {
                from = this.addDays(from, -1);
            }
            while (isLeave(this.toYMD(this.addDays(to, 1)))) {
                to = this.addDays(to, 1);
            }

            return { from: this.toYMD(from), to: this.toYMD(to) };
        },

        closeEditor() {
            this.editing = null;
        },

        async save() {
            this.saving = true;
            try {
                // Congé sur une période → un pointage "congé" par jour
                if (this.form.status == "leave") {
                    // Modification d'un congé existant : on retire d'abord
                    // toute l'ancienne période (gère la RÉDUCTION des jours).
                    if (this.editing.leaveOriginal) {
                        const o = this.editing.leaveOriginal;
                        await attendanceService.removeLeave(this.project.slug, {
                            user_id: this.editing.employee.id,
                            from: o.from,
                            to: o.to,
                        });
                        this.attendances = this.attendances.filter((att) => {
                            if (
                                att.user_id != this.editing.employee.id ||
                                att.status != "leave"
                            ) {
                                return true;
                            }
                            const d = this.toYMD(new Date(att.date));
                            return d < o.from || d > o.to;
                        });
                    }

                    const { data } = await attendanceService.leave(
                        this.project.slug,
                        {
                            user_id: this.editing.employee.id,
                            from: this.form.leave_from,
                            to: this.form.leave_to,
                            note: this.form.note,
                        }
                    );
                    (Array.isArray(data) ? data : []).forEach((a) =>
                        this.upsertLocal(a)
                    );
                    this.closeEditor();
                    return;
                }

                const payload = {
                    user_id: this.editing.employee.id,
                    date: this.editing.date,
                    status: this.form.status,
                    note: this.form.note,
                };

                if (this.form.status == "present") {
                    payload.expected_arrival = this.form.expected_arrival || null;
                    payload.expected_departure = this.form.expected_departure || null;
                    payload.arrival_at = this.combine(this.editing.date, this.form.arrival);
                    payload.departure_at = this.combine(this.editing.date, this.form.departure);
                    payload.break_minutes = this.form.break_minutes || 0;

                    // Enregistre les horaires habituels de l'employé si demandé.
                    // (modifier un jour précis n'écrase PAS ces valeurs par défaut)
                    if (this.form.save_as_default) {
                        this.saveEmployeeDefault(this.editing.employee.id, {
                            expected_arrival: this.form.expected_arrival,
                            expected_departure: this.form.expected_departure,
                            break_minutes: this.form.break_minutes,
                        });
                    }
                } else {
                    payload.expected_arrival = null;
                    payload.expected_departure = null;
                    payload.arrival_at = null;
                    payload.departure_at = null;
                    payload.break_minutes = 0;
                }

                const { data } = await attendanceService.store(
                    this.project.slug,
                    payload
                );
                this.upsertLocal(data);
                this.closeEditor();
            } finally {
                this.saving = false;
            }
        },

        async remove() {
            this.saving = true;
            try {
                // Congé : on supprime toute la période Du → Au
                if (this.form.status == "leave") {
                    await attendanceService.removeLeave(this.project.slug, {
                        user_id: this.editing.employee.id,
                        from: this.form.leave_from,
                        to: this.form.leave_to,
                    });
                    this.attendances = this.attendances.filter((att) => {
                        if (
                            att.user_id != this.editing.employee.id ||
                            att.status != "leave"
                        ) {
                            return true;
                        }
                        const d = this.toYMD(new Date(att.date));
                        return (
                            d < this.form.leave_from || d > this.form.leave_to
                        );
                    });
                    this.closeEditor();
                    return;
                }

                await attendanceService.destroy(this.project.slug, this.editing.id);
                this.attendances = this.attendances.filter(
                    (a) => a.id != this.editing.id
                );
                this.closeEditor();
            } finally {
                this.saving = false;
            }
        },

        upsertLocal(attendance) {
            const i = this.attendances.findIndex((a) => a.id == attendance.id);
            if (i >= 0) {
                this.attendances.splice(i, 1, attendance);
            } else {
                this.attendances.push(attendance);
            }
        },
    },

    computed: {
        ...mapGetters(["project"]),

        // Pause saisie en h:min mais stockée/calculée en minutes
        breakTime: {
            get() {
                const m = this.form.break_minutes || 0;
                const h = Math.floor(m / 60);
                const mm = m % 60;
                return (
                    String(h).padStart(2, "0") +
                    ":" +
                    String(mm).padStart(2, "0")
                );
            },
            set(value) {
                if (!value) {
                    this.form.break_minutes = 0;
                    return;
                }
                const [h, m] = value.split(":").map(Number);
                this.form.break_minutes = (h || 0) * 60 + (m || 0);
            },
        },

        days() {
            return DAY_LABELS.map((label, i) => {
                const d = this.addDays(this.weekStart, i);
                return { date: this.toYMD(d), label: label + " " + d.getDate() };
            });
        },

        weekLabel() {
            const start = this.weekStart;
            const end = this.addDays(this.weekStart, 4);
            return (
                "Semaine du " +
                start.getDate() +
                " – " +
                end.getDate() +
                " " +
                MONTHS[end.getMonth()]
            );
        },

        livePreview() {
            if (this.form.status != "present" || !this.form.arrival) return "";
            let out = "";
            if (this.form.expected_arrival && this.form.arrival > this.form.expected_arrival) {
                const [eh, em] = this.form.expected_arrival.split(":").map(Number);
                const [ah, am] = this.form.arrival.split(":").map(Number);
                const retard = ah * 60 + am - (eh * 60 + em);
                if (retard > 0) out += "Retard : <b>" + retard + " min</b>";
            }
            return out;
        },
    },
};
</script>

<style scoped>
.hc-planning {
    padding: 12px;
    width: 100%;
    height: 100%;
    overflow: auto;
}

.hc-planning-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 12px;
}

.hc-planning-week {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    font-weight: 500;
}

.hc-planning-nav {
    cursor: pointer;
    color: #555;
    padding: 4px 8px;
    border-radius: 6px;
    text-decoration: none;
}
.hc-planning-nav:hover {
    background-color: #f0f0f0;
}

.hc-planning-legend {
    display: flex;
    gap: 14px;
    font-size: 12px;
    color: #666;
}
.hc-planning-legend .dot {
    display: inline-block;
    width: 9px;
    height: 9px;
    border-radius: 50%;
    margin-right: 3px;
}
.dot.present { background-color: #2abb47; }
.dot.retard { background-color: #ef9f27; }
.dot.conge { background-color: #1a73e8; }
.dot.pause { background-color: #9a70ff; }
.dot.absent { background-color: #e24b4a; }

.hc-planning-table-wrapper {
    position: relative;
    border: 1px solid #e6e6e6;
    border-radius: 10px;
    overflow: hidden;
}

.hc-planning-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.hc-planning-table th {
    background-color: #f7f7f7;
    font-size: 12px;
    font-weight: 500;
    color: #666;
    padding: 8px 4px;
    text-align: center;
    border-bottom: 1px solid #e6e6e6;
}

.hc-planning-table th.hc-planning-emp,
.hc-planning-table td.hc-planning-emp {
    width: 20%;
    text-align: left;
    padding-left: 10px;
}

.hc-planning-emp-name {
    font-size: 13px;
    font-weight: 500;
    color: #222;
}
.hc-planning-emp-sub {
    font-size: 11px;
    color: #888;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.hc-planning-cell {
    text-align: center;
    padding: 7px 3px;
    border-bottom: 1px solid #eee;
    border-left: 1px solid #f2f2f2;
    cursor: pointer;
    vertical-align: top;
}
.hc-planning-cell:hover {
    background-color: #f6faff;
}

.hc-planning-times {
    font-family: monospace;
    font-size: 11px;
    color: #333;
    margin-bottom: 3px;
}

.hc-planning-pill {
    display: inline-block;
    padding: 1px 6px;
    border-radius: 6px;
    font-size: 11px;
}
.hc-planning-pill.present { background-color: #e1f5e6; color: #1a7a33; }
.hc-planning-pill.retard { background-color: #faeeda; color: #8a5a0b; }
.hc-planning-pill.conge { background-color: #e6f1fb; color: #12508a; }
.hc-planning-pill.pause { background-color: #f0e8ff; color: #5d3abe; }
.hc-planning-pill.absent { background-color: #fcebeb; color: #a32d2d; }

.hc-planning-empty {
    color: #ccc;
    font-size: 14px;
}

.hc-planning-total {
    text-align: center;
    font-size: 11px;
    color: #666;
    border-bottom: 1px solid #eee;
    border-left: 1px solid #f2f2f2;
    vertical-align: top;
    padding: 8px 4px;
}

.hc-planning-none {
    text-align: center;
    color: #999;
    padding: 20px;
    font-size: 13px;
}

.hc-planning-overlay {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
}

.hc-planning-editor {
    background-color: white;
    width: 360px;
    max-width: 92vw;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.hc-planning-editor-header {
    display: flex;
    align-items: start;
    justify-content: space-between;
    margin-bottom: 12px;
}
.hc-planning-editor-title {
    font-size: 15px;
    font-weight: 500;
}

.hc-planning-field {
    margin-bottom: 10px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
}
.hc-planning-field label {
    font-size: 12px;
    color: #666;
}
.hc-planning-field input,
.hc-planning-field select,
.hc-planning-field textarea {
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 6px 8px;
    font-size: 13px;
    width: 100%;
}
.hc-planning-field-row {
    display: flex;
    gap: 10px;
}

.hc-planning-preview {
    font-size: 12px;
    color: #8a5a0b;
    background-color: #faeeda;
    padding: 6px 8px;
    border-radius: 6px;
    margin-bottom: 10px;
}

.hc-planning-editor-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 8px;
    position: relative;
}
.hc-planning-btn {
    border: none;
    border-radius: 7px;
    padding: 8px 14px;
    font-size: 13px;
    cursor: pointer;
}
.hc-planning-btn.primary {
    background-color: #12a0f3;
    color: white;
}
.hc-planning-btn.danger {
    background-color: #fcebeb;
    color: #a32d2d;
}
</style>
