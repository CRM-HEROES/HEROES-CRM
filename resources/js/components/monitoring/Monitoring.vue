<template>
    <div id="hc-monitoring">
        <div class="mon-header">
            <div class="mon-title">
                <span class="mon-logo"><svg viewBox="0 0 24 24"><path d="M12 2l8 3v6c0 5-3.4 9.4-8 11-4.6-1.6-8-6-8-11V5z"/><path d="M13 7l-3 6h3l-1 4 4-6h-3z"/></svg></span>
                <div>
                    <h1>Centre de <em>Monitoring</em></h1>
                    <span class="mon-sub" v-if="overview && overview.collected_at">
                        <i class="mon-live"></i> Dernière mesure : {{ formatDate(overview.collected_at) }}
                        · actualisation toutes les {{ refreshSeconds }} s
                    </span>
                </div>
            </div>
            <div class="mon-periods">
                <button v-for="p in periods" :key="p.key" :class="{ active: period === p.key }" @click="changePeriod(p.key)">
                    {{ p.label }}
                </button>
            </div>
        </div>

        <div v-if="loading" class="mon-state">Chargement…</div>
        <div v-else-if="error" class="mon-state mon-error">{{ error }}</div>
        <div v-else-if="overview && !overview.available" class="mon-state mon-error">{{ overview.error }}</div>

        <template v-else-if="overview">
            <div v-if="overview.errors && overview.errors.length" class="mon-warning">
                Certaines métriques sont temporairement indisponibles : {{ overview.errors.join(", ") }}.
            </div>

            <div class="mon-cards">
                <div class="mon-card" :class="'lv-' + level(overview.cpu.percent)" v-if="overview.cpu">
                    <div class="mon-card-head">
                        <span class="mon-ico"><svg viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12" rx="2"/><path d="M9 2v3M15 2v3M9 19v3M15 19v3M2 9h3M2 15h3M19 9h3M19 15h3"/></svg></span>
                        <span class="mon-card-title">CPU</span>
                        <span class="mon-badge" :class="level(overview.cpu.percent)">{{ levelLabel(overview.cpu.percent) }}</span>
                    </div>
                    <div class="mon-card-body">
                        <div class="mon-gauge">
                            <svg viewBox="0 0 100 100">
                                <circle class="g-bg" cx="50" cy="50" r="42" />
                                <circle class="g-fg" cx="50" cy="50" r="42" :stroke-dasharray="dash(overview.cpu.percent)" />
                            </svg>
                            <div class="g-val">{{ pct(overview.cpu.percent) }}</div>
                        </div>
                        <div class="mon-card-meta"><span><b>{{ overview.cpu.cores }}</b> cœurs</span>
                            <span v-if="overview.cpu.load">Charge <b>{{ overview.cpu.load.join(" / ") }}</b></span></div>
                    </div>
                </div>
                <div class="mon-card" v-else><div class="mon-card-head"><span class="mon-ico"><svg viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12" rx="2"/><path d="M9 2v3M15 2v3M9 19v3M15 19v3M2 9h3M2 15h3M19 9h3M19 15h3"/></svg></span><span class="mon-card-title">CPU</span></div><div class="mon-na">Indisponible</div></div>
                <div class="mon-card" :class="'lv-' + level(overview.ram.percent)" v-if="overview.ram">
                    <div class="mon-card-head">
                        <span class="mon-ico"><svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="10" rx="2"/><path d="M6 17v3M10 17v3M14 17v3M18 17v3M6 11v2M10 11v2M14 11v2M18 11v2"/></svg></span>
                        <span class="mon-card-title">RAM</span>
                        <span class="mon-badge" :class="level(overview.ram.percent)">{{ levelLabel(overview.ram.percent) }}</span>
                    </div>
                    <div class="mon-card-body">
                        <div class="mon-gauge">
                            <svg viewBox="0 0 100 100">
                                <circle class="g-bg" cx="50" cy="50" r="42" />
                                <circle class="g-fg" cx="50" cy="50" r="42" :stroke-dasharray="dash(overview.ram.percent)" />
                            </svg>
                            <div class="g-val">{{ pct(overview.ram.percent) }}</div>
                        </div>
                        <div class="mon-card-meta"><span><b>{{ bytes(overview.ram.used) }}</b> / {{ bytes(overview.ram.total) }}</span>
                            <span><b>{{ bytes(overview.ram.available) }}</b> disponibles</span></div>
                    </div>
                </div>
                <div class="mon-card" v-else><div class="mon-card-head"><span class="mon-ico"><svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="10" rx="2"/><path d="M6 17v3M10 17v3M14 17v3M18 17v3M6 11v2M10 11v2M14 11v2M18 11v2"/></svg></span><span class="mon-card-title">RAM</span></div><div class="mon-na">Indisponible</div></div>
                <div class="mon-card" :class="'lv-' + level(overview.disk.percent)" v-if="overview.disk">
                    <div class="mon-card-head">
                        <span class="mon-ico"><svg viewBox="0 0 24 24"><ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v12c0 1.7 3.6 3 8 3s8-1.3 8-3V6M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/></svg></span>
                        <span class="mon-card-title">Disque</span>
                        <span class="mon-badge" :class="level(overview.disk.percent)">{{ levelLabel(overview.disk.percent) }}</span>
                    </div>
                    <div class="mon-card-body">
                        <div class="mon-gauge">
                            <svg viewBox="0 0 100 100">
                                <circle class="g-bg" cx="50" cy="50" r="42" />
                                <circle class="g-fg" cx="50" cy="50" r="42" :stroke-dasharray="dash(overview.disk.percent)" />
                            </svg>
                            <div class="g-val">{{ pct(overview.disk.percent) }}</div>
                        </div>
                        <div class="mon-card-meta"><span><b>{{ bytes(overview.disk.used) }}</b> / {{ bytes(overview.disk.total) }}</span>
                            <span><b>{{ bytes(overview.disk.free) }}</b> libres</span></div>
                    </div>
                </div>
                <div class="mon-card" v-else><div class="mon-card-head"><span class="mon-ico"><svg viewBox="0 0 24 24"><ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v12c0 1.7 3.6 3 8 3s8-1.3 8-3V6M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/></svg></span><span class="mon-card-title">Disque</span></div><div class="mon-na">Indisponible</div></div>
                <div class="mon-card lv-ok">
                    <div class="mon-card-head">
                        <span class="mon-ico"><svg viewBox="0 0 24 24"><path d="M12 3v14M7 12l5 5 5-5M5 21h14"/></svg></span>
                        <span class="mon-card-title">Réseau</span>
                        <span class="mon-badge ok">Live</span>
                    </div>
                    <template v-if="overview.network">
                        <div class="mon-net">
                            <div class="mon-net-row down"><span class="arrow">↓</span><div><small>Entrant</small><strong>{{ rate(overview.network.rx_bps) }}</strong></div></div>
                            <div class="mon-net-row up"><span class="arrow">↑</span><div><small>Sortant</small><strong>{{ rate(overview.network.tx_bps) }}</strong></div></div>
                        </div>
                        <div class="mon-card-meta flat">
                            <span>Reçu <b>{{ bytes(overview.network.rx_total) }}</b></span>
                            <span>Envoyé <b>{{ bytes(overview.network.tx_total) }}</b></span>
                        </div>
                    </template>
                    <div v-else class="mon-na">Indisponible</div>
                </div>
            </div>

            <section class="mon-panel">
                <div class="mon-panel-head">
                    <span class="mon-num">01</span>
                    <div><h2>Qui charge le serveur ?</h2><span class="mon-sub">Classement en direct des processus et conteneurs les plus gourmands</span></div>
                </div>
                <div class="mon-tops">
                    <div class="mon-top cpu">
                        <h3>Processus · CPU</h3>
                        <div class="mon-rank">
                            <div class="mon-rank-row" v-for="(it, i) in topCpu" :key="i">
                                <span class="mon-rank-n">{{ i + 1 }}</span>
                                <span class="mon-rank-name" :title="it.name">{{ it.name }}</span>
                                <span class="mon-rank-val">{{ pct(it.cpu) }}</span>
                                <div class="mon-rank-bar"><div :style="{ width: rel(it.cpu, topCpu) }"></div></div>
                            </div>
                            <div v-if="!topCpu.length" class="mon-na">Aucune donnée</div>
                        </div>
                    </div>
                    <div class="mon-top ram">
                        <h3>Processus · RAM</h3>
                        <div class="mon-rank">
                            <div class="mon-rank-row" v-for="(it, i) in topRam" :key="i">
                                <span class="mon-rank-n">{{ i + 1 }}</span>
                                <span class="mon-rank-name" :title="it.name">{{ it.name }}</span>
                                <span class="mon-rank-val">{{ bytes(it.ram_bytes) }}</span>
                                <div class="mon-rank-bar"><div :style="{ width: rel(it.ram_bytes, topRam, 'ram_bytes') }"></div></div>
                            </div>
                            <div v-if="!topRam.length" class="mon-na">Aucune donnée</div>
                        </div>
                    </div>
                    <div class="mon-top dock">
                        <h3>Conteneurs Docker · CPU</h3>
                        <div class="mon-rank">
                            <div class="mon-rank-row" v-for="(it, i) in topDocker" :key="i">
                                <span class="mon-rank-n">{{ i + 1 }}</span>
                                <span class="mon-rank-name" :title="it.name">{{ it.name }}</span>
                                <span class="mon-rank-val">{{ pct(it.cpu) }}</span>
                                <div class="mon-rank-bar"><div :style="{ width: rel(it.cpu, topDocker) }"></div></div>
                            </div>
                            <div v-if="!topDocker.length" class="mon-na">Aucune donnée</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mon-panel">
                <div class="mon-panel-head">
                    <span class="mon-num">02</span>
                    <div><h2>Historique des performances</h2></div>
                </div>
                <div v-if="historyError" class="mon-state mon-error">{{ historyError }}</div>
                <div v-else-if="history && !history.points.length" class="mon-state">
                    Aucun historique pour cette période. Les mesures sont enregistrées chaque minute
                    par le planificateur Laravel (rétention : {{ history.retention_days }} jours).
                </div>
                <div v-else class="mon-charts">
                    <div class="mon-chart"><h3><i style="background:#7939b8"></i>CPU (%)</h3><div class="mon-canvas"><canvas ref="cpuChart"></canvas></div></div>
                    <div class="mon-chart"><h3><i style="background:#0284c7"></i>RAM (%)</h3><div class="mon-canvas"><canvas ref="ramChart"></canvas></div></div>
                    <div class="mon-chart"><h3><i style="background:#059669"></i>Réseau</h3><div class="mon-canvas"><canvas ref="netChart"></canvas></div></div>
                </div>
            </section>
            <section class="mon-panel">
                <div class="mon-panel-head">
                    <span class="mon-num">03</span>
                    <div><h2>Pics de consommation</h2><span class="mon-sub" v-if="history">Seuils : CPU ≥ {{ history.thresholds.cpu }} % · RAM ≥ {{ history.thresholds.ram }} %</span></div>
                </div>
                <div v-if="!history || !history.peaks.length" class="mon-state">Aucun pic détecté sur la période.</div>
                <div v-else class="mon-table-wrap">
                <table class="mon-table">
                    <thead>
                        <tr><th>Date</th><th>Type</th><th>CPU max</th><th>RAM max</th><th>Responsables</th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="(peak, i) in history.peaks" :key="i">
                            <td>{{ formatDate(peak.started_at) }}<span v-if="peak.ended_at !== peak.started_at"> → {{ formatTime(peak.ended_at) }}</span></td>
                            <td><span class="mon-badge bad" v-for="t in peak.type" :key="t">{{ t.toUpperCase() }}</span></td>
                            <td>{{ pct(peak.cpu_max) }}</td>
                            <td>{{ pct(peak.ram_max) }}</td>
                            <td>
                                <template v-if="peak.details">
                                    <span v-for="(p, j) in peak.details.processes || []" :key="'p' + j" class="mon-chip">
                                        {{ p.name }}<template v-if="p.cpu !== null"> {{ p.cpu }}%</template>
                                    </span>
                                    <span v-for="(c, j) in peak.details.containers || []" :key="'c' + j" class="mon-chip mon-chip-docker">
                                        {{ c.name }}<template v-if="c.cpu !== null"> {{ c.cpu }}%</template>
                                    </span>
                                </template>
                                <span v-else class="mon-na">Non disponible</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </section>
            <section class="mon-panel">
                <div class="mon-panel-head">
                    <span class="mon-num">04</span>
                    <div><h2>Processus les plus gourmands</h2></div>
                </div>
                <div v-if="!overview.processes" class="mon-state">Liste des processus indisponible.</div>
                <div v-else class="mon-table-wrap">
                <table class="mon-table">
                    <thead>
                        <tr>
                            <th>Processus</th>
                            <th>PID</th>
                            <th class="sortable" @click="sortProcesses('cpu')">CPU {{ arrow(procSort, 'cpu') }}</th>
                            <th class="sortable" @click="sortProcesses('ram_bytes')">RAM {{ arrow(procSort, 'ram_bytes') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in sortedProcesses" :key="p.pid">
                            <td class="strong">{{ p.name }}</td>
                            <td class="mon-na">{{ p.pid }}</td>
                            <td>{{ p.cpu === null ? "—" : pct(p.cpu) }}</td>
                            <td>{{ bytes(p.ram_bytes) }} <span class="mon-na">({{ pct(p.ram_percent) }})</span></td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </section>
            <section class="mon-panel">
                <div class="mon-panel-head">
                    <span class="mon-num">05</span>
                    <div><h2>Conteneurs Docker</h2></div>
                </div>
                <div v-if="!overview.containers || !overview.containers.available" class="mon-state">
                    {{ (overview.containers && overview.containers.error) || "Indisponible" }}
                </div>
                <div v-else class="mon-table-wrap">
                <table class="mon-table">
                    <thead>
                        <tr>
                            <th>Conteneur</th>
                            <th>Image</th>
                            <th class="sortable" @click="sortContainers('cpu')">CPU {{ arrow(contSort, 'cpu') }}</th>
                            <th class="sortable" @click="sortContainers('ram_bytes')">RAM {{ arrow(contSort, 'ram_bytes') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c in sortedContainers" :key="c.name">
                            <td class="strong">{{ c.name }}</td>
                            <td class="mon-na">{{ c.image }}</td>
                            <td>{{ c.cpu === null ? "—" : pct(c.cpu) }}</td>
                            <td>
                                {{ bytes(c.ram_bytes) }}
                                <span v-if="c.ram_percent !== null" class="mon-na">({{ pct(c.ram_percent) }})</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </section>
        </template>
    </div>
</template>

<script>
import MonitoringService from "@/apis/monitoring";
import ChartJsInit from "@/utils/chart";
import ChartDateFnsInit from "@/utils/chart-date-fns";

export default {
    data() {
        return {
            refreshSeconds: 20,
            periods: [
                { key: "1h", label: "Dernière heure" },
                { key: "24h", label: "24 heures" },
                { key: "7d", label: "7 jours" },
            ],
            period: "1h",
            loading: true,
            error: null,
            overview: null,
            history: null,
            historyError: null,
            procSort: { key: "cpu", dir: -1 },
            contSort: { key: "cpu", dir: -1 },
            charts: {},
            timer: null,
            historyTick: 0,
            chartsReady: false,
        };
    },

    async mounted() {
        try {
            // The date adapter needs the global Chart to exist: load sequentially
            await ChartJsInit();
            await ChartDateFnsInit();
            this.chartsReady = true;
        } catch (e) {
            // Charts are optional: tables and cards still work
        }
        await this.refresh(true);
        this.loading = false;
        this.timer = setInterval(() => {
            if (document.hidden) return;
            this.refresh(false);
        }, this.refreshSeconds * 1000);
    },

    beforeUnmount() {
        clearInterval(this.timer);
        Object.values(this.charts).forEach((c) => c && c.destroy());
    },

    computed: {
        topCpu() {
            return this.sortBy((this.overview.processes || []).filter((p) => p.cpu !== null), { key: "cpu", dir: -1 }).slice(0, 5);
        },
        topRam() {
            return this.sortBy(this.overview.processes || [], { key: "ram_bytes", dir: -1 }).slice(0, 5);
        },
        topDocker() {
            const c = this.overview.containers;
            return c && c.available ? this.sortBy((c.items || []).filter((x) => x.cpu !== null), { key: "cpu", dir: -1 }).slice(0, 5) : [];
        },
        sortedProcesses() {
            return this.sortBy(this.overview.processes || [], this.procSort);
        },
        sortedContainers() {
            return this.sortBy(this.overview.containers.items || [], this.contSort);
        },
    },

    methods: {
        async refresh(withHistory) {
            try {
                const { data } = await MonitoringService.overview();
                this.overview = data;
                this.error = null;
            } catch (e) {
                this.error = "Impossible de récupérer les métriques du serveur pour le moment.";
            }

            // History is sampled once per minute: reload it every 3rd refresh
            this.historyTick++;
            if (withHistory || this.historyTick % 3 === 0) {
                await this.loadHistory();
            }
        },

        async loadHistory() {
            try {
                const { data } = await MonitoringService.history(this.period);
                this.history = data;
                this.historyError = null;
            } catch (e) {
                this.historyError = "Impossible de récupérer l'historique.";
            }
            this.$nextTick(() => this.drawCharts());
        },

        changePeriod(key) {
            this.period = key;
            this.loadHistory();
        },

        drawCharts() {
            if (!this.chartsReady || !this.history || !this.history.points.length || !this.$refs.cpuChart) {
                return;
            }
            const pts = this.history.points;
            const labels = pts.map((p) => new Date(p.t));
            const time = this.period === "7d"
                ? { unit: "day", displayFormats: { day: "dd/MM" } }
                : { unit: this.period === "1h" ? "minute" : "hour", displayFormats: { minute: "HH:mm", hour: "HH:mm" } };

            const make = (ref, datasets, yExtra, tick) => {
                if (this.charts[ref]) this.charts[ref].destroy();
                this.charts[ref] = new Chart(this.$refs[ref], {
                    type: "line",
                    data: { labels, datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        interaction: { mode: "index", intersect: false },
                        elements: { point: { radius: 0 }, line: { tension: 0.25, borderWidth: 2 } },
                        plugins: {
                            legend: { display: datasets.length > 1, labels: { color: "#4b4770", usePointStyle: true, boxWidth: 8 } },
                            tooltip: { backgroundColor: "#ffffff", borderColor: "#e8e4f4", borderWidth: 1, titleColor: "#1e1b3a", bodyColor: "#4b4770" },
                        },
                        scales: {
                            x: { type: "time", time, ticks: { maxTicksLimit: 8, color: "#6e6a92" }, grid: { color: "#eee9f8" }, border: { display: false } },
                            y: { beginAtZero: true, ...yExtra, ticks: { callback: tick, color: "#6e6a92" }, grid: { color: "#eee9f8" }, border: { display: false } },
                        },
                    },
                });
            };

            make("cpuChart", [
                { label: "CPU moyen", data: pts.map((p) => p.cpu), borderColor: "#7939b8", backgroundColor: "#7939b81f", fill: true },
                { label: "CPU max", data: pts.map((p) => p.cpu_max), borderColor: "#d99a06", borderDash: [4, 3] },
            ], { max: 100 }, (v) => v + "%");
            make("ramChart", [
                { label: "RAM", data: pts.map((p) => p.ram), borderColor: "#0284c7", backgroundColor: "#0284c71a", fill: true },
            ], { max: 100 }, (v) => v + "%");
            make("netChart", [
                { label: "Entrant", data: pts.map((p) => p.rx_bps), borderColor: "#059669" },
                { label: "Sortant", data: pts.map((p) => p.tx_bps), borderColor: "#7939b8" },
            ], {}, (v) => this.rate(v));
        },

        sortBy(list, sort) {
            return [...list].sort((a, b) => ((a[sort.key] ?? -1) - (b[sort.key] ?? -1)) * sort.dir);
        },
        sortProcesses(key) {
            this.procSort = { key, dir: this.procSort.key === key ? -this.procSort.dir : -1 };
        },
        sortContainers(key) {
            this.contSort = { key, dir: this.contSort.key === key ? -this.contSort.dir : -1 };
        },
        arrow(sort, key) {
            return sort.key === key ? (sort.dir === -1 ? "▼" : "▲") : "";
        },

        pct(v) {
            return v === null || v === undefined ? "—" : `${v} %`;
        },
        rel(v, list, key = "cpu") {
            const max = Math.max(...list.map((x) => x[key] || 0), 1);
            return `${Math.max(2, Math.min(100, ((v || 0) / max) * 100))}%`;
        },
        dash(v) {
            const p = Math.max(0, Math.min(100, v || 0));
            return `${(p / 100) * 264} 264`;
        },
        width(v) {
            return `${Math.max(0, Math.min(100, v || 0))}%`;
        },
        level(v) {
            return v >= 90 ? "bad" : v >= 75 ? "warn" : "ok";
        },
        levelLabel(v) {
            return v >= 90 ? "Critique" : v >= 75 ? "Élevé" : "Optimal";
        },
        bytes(v) {
            if (v === null || v === undefined) return "—";
            const u = ["o", "Ko", "Mo", "Go", "To"];
            let i = 0;
            while (v >= 1024 && i < u.length - 1) {
                v /= 1024;
                i++;
            }
            return `${v.toFixed(i > 1 ? 1 : 0)} ${u[i]}`;
        },
        rate(v) {
            return v === null || v === undefined ? "—" : `${this.bytes(v)}/s`;
        },
        formatDate(iso) {
            return new Date(iso).toLocaleString("fr-FR", { day: "2-digit", month: "2-digit", hour: "2-digit", minute: "2-digit" });
        },
        formatTime(iso) {
            return new Date(iso).toLocaleTimeString("fr-FR", { hour: "2-digit", minute: "2-digit" });
        },
    },
};
</script>

<style>
#hc-monitoring {
    --m-bg: #f4f2fb;
    --m-panel: #ffffff;
    --m-line: #e8e4f4;
    --m-text: #1e1b3a;
    --m-mute: #6e6a92;
    --m-purple: #7939b8;
    --m-violet: #9d5bd8;
    --m-gold: #d99a06;
    --m-ok: #10b981;
    --m-warn: #f59e0b;
    --m-bad: #ef4444;
    width: 100%;
    height: 100%;
    overflow: auto;
    padding: 28px 32px 56px;
    color: var(--m-text);
    box-sizing: border-box;
    background:
        radial-gradient(800px 320px at 90% -10%, #7939b81f, transparent 60%),
        radial-gradient(600px 300px at -5% 0%, #fbbf2420, transparent 60%),
        var(--m-bg);
}
#hc-monitoring h1 { font-size: 26px; margin: 0 0 6px; font-weight: 700; letter-spacing: -0.01em; color: var(--m-text); }
#hc-monitoring h1 em { font-style: normal; background: linear-gradient(90deg, var(--m-purple), var(--m-gold)); -webkit-background-clip: text; background-clip: text; color: transparent; }
#hc-monitoring h2 { font-size: 17px; margin: 0; font-weight: 600; color: var(--m-text); }
#hc-monitoring h3 { font-size: 13px; margin: 0 0 12px; color: #3d3963; font-weight: 600; display: flex; align-items: center; gap: 8px; }
#hc-monitoring h3 i { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
#hc-monitoring .mon-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 28px; }
#hc-monitoring .mon-title { display: flex; align-items: center; gap: 16px; }
#hc-monitoring .mon-logo { width: 52px; height: 52px; border-radius: 16px; display: grid; place-items: center; background: linear-gradient(135deg, var(--m-violet), var(--m-purple)); box-shadow: 0 8px 22px #7939b83d, inset 0 1px 0 #ffffff60; }
#hc-monitoring .mon-logo svg { width: 28px; height: 28px; fill: none; stroke: #fff; stroke-width: 1.6; stroke-linejoin: round; stroke-linecap: round; }
#hc-monitoring .mon-sub { font-size: 12px; color: var(--m-mute); display: inline-flex; align-items: center; gap: 8px; }
#hc-monitoring .mon-live { width: 8px; height: 8px; border-radius: 50%; background: var(--m-ok); box-shadow: 0 0 0 0 #10b98180; animation: mon-pulse 2s infinite; }
@keyframes mon-pulse { 70% { box-shadow: 0 0 0 8px #10b98100; } 100% { box-shadow: 0 0 0 0 #10b98100; } }
#hc-monitoring .mon-periods { display: inline-flex; padding: 4px; gap: 4px; background: var(--m-panel); border: 1px solid var(--m-line); border-radius: 14px; box-shadow: 0 2px 8px #7939b80f; }
#hc-monitoring .mon-periods button { border: 0; background: transparent; color: var(--m-mute); padding: 8px 16px; cursor: pointer; font-size: 13px; font-weight: 500; border-radius: 10px; transition: all .2s; }
#hc-monitoring .mon-periods button:hover { color: var(--m-purple); }
#hc-monitoring .mon-periods button.active { background: linear-gradient(135deg, var(--m-violet), var(--m-purple)); color: #fff; box-shadow: 0 4px 12px #7939b84d; }
#hc-monitoring .mon-state { padding: 20px; background: var(--m-panel); border: 1px dashed #d4cdea; border-radius: 14px; color: var(--m-mute); font-size: 13px; }
#hc-monitoring .mon-error { color: #b91c1c; background: #fef2f2; border-color: #fecaca; }
#hc-monitoring .mon-warning { padding: 12px 16px; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; border-radius: 12px; margin-bottom: 18px; font-size: 13px; }

#hc-monitoring .mon-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-bottom: 32px; }
#hc-monitoring .mon-card { position: relative; overflow: hidden; padding: 20px; border-radius: 20px; background: var(--m-panel); border: 1px solid var(--m-line); box-shadow: 0 6px 24px #3b1d6a12; transition: transform .25s, box-shadow .25s; --acc: var(--m-ok); }
#hc-monitoring .mon-card::before { content: ""; position: absolute; left: 0; right: 0; top: 0; height: 4px; background: linear-gradient(90deg, var(--acc), transparent); }
#hc-monitoring .mon-card::after { content: ""; position: absolute; width: 160px; height: 160px; right: -60px; bottom: -70px; border-radius: 50%; background: radial-gradient(var(--acc), transparent 70%); opacity: .10; pointer-events: none; }
#hc-monitoring .mon-card:hover { transform: translateY(-3px); box-shadow: 0 14px 34px #7939b826; }
#hc-monitoring .mon-card.lv-warn { --acc: var(--m-warn); }
#hc-monitoring .mon-card.lv-bad { --acc: var(--m-bad); }
#hc-monitoring .mon-card-head { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
#hc-monitoring .mon-ico { width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center; background: #7939b816; }
#hc-monitoring .mon-ico svg { width: 18px; height: 18px; fill: none; stroke: var(--m-purple); stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
#hc-monitoring .mon-card-title { font-size: 12px; text-transform: uppercase; letter-spacing: .12em; color: #3d3963; font-weight: 700; flex: 1; }
#hc-monitoring .mon-badge { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 20px; margin-right: 4px; }
#hc-monitoring .mon-badge.ok { color: #047857; background: #d1fae5; }
#hc-monitoring .mon-badge.warn { color: #b45309; background: #fef3c7; }
#hc-monitoring .mon-badge.bad { color: #b91c1c; background: #fee2e2; }
#hc-monitoring .mon-card-body { display: flex; align-items: center; gap: 18px; }
#hc-monitoring .mon-gauge { position: relative; width: 104px; height: 104px; flex: none; }
#hc-monitoring .mon-gauge svg { width: 100%; height: 100%; transform: rotate(-90deg); }
#hc-monitoring .mon-gauge circle { fill: none; stroke-width: 9; stroke-linecap: round; }
#hc-monitoring .g-bg { stroke: #efeaf9; }
#hc-monitoring .g-fg { stroke: var(--acc); transition: stroke-dasharray .6s ease; }
#hc-monitoring .g-val { position: absolute; inset: 0; display: grid; place-items: center; font-size: 19px; font-weight: 700; color: var(--m-text); }
#hc-monitoring .mon-card-meta { display: flex; flex-direction: column; gap: 6px; font-size: 12px; color: var(--m-mute); }
#hc-monitoring .mon-card-meta b { color: var(--m-text); font-weight: 600; }
#hc-monitoring .mon-card-meta.flat { flex-direction: row; justify-content: space-between; margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--m-line); }
#hc-monitoring .mon-net { display: flex; flex-direction: column; gap: 10px; }
#hc-monitoring .mon-net-row { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 12px; background: #f7f5fd; }
#hc-monitoring .mon-net-row .arrow { width: 30px; height: 30px; border-radius: 50%; display: grid; place-items: center; font-weight: 700; }
#hc-monitoring .mon-net-row.down .arrow { background: #d1fae5; color: #047857; }
#hc-monitoring .mon-net-row.up .arrow { background: #ede9fe; color: var(--m-purple); }
#hc-monitoring .mon-net-row small { display: block; font-size: 11px; color: var(--m-mute); }
#hc-monitoring .mon-net-row strong { font-size: 17px; color: var(--m-text); }

#hc-monitoring .mon-panel { position: relative; padding: 24px; margin-bottom: 28px; border-radius: 22px; background: var(--m-panel); border: 1px solid var(--m-line); box-shadow: 0 8px 30px #3b1d6a10; }
#hc-monitoring .mon-panel::before { content: ""; position: absolute; left: 0; top: 28px; bottom: 28px; width: 4px; border-radius: 0 4px 4px 0; background: linear-gradient(var(--m-violet), var(--m-gold)); }
#hc-monitoring .mon-panel-head { display: flex; align-items: center; gap: 14px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--m-line); }
#hc-monitoring .mon-num { font-size: 12px; font-weight: 700; letter-spacing: .1em; color: #a16c00; padding: 6px 10px; border-radius: 10px; background: #fef3c7; }
#hc-monitoring .mon-charts { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 18px; }
#hc-monitoring .mon-chart { padding: 16px; border-radius: 16px; background: #faf9fe; border: 1px solid var(--m-line); }
#hc-monitoring .mon-canvas { position: relative; height: 200px; }

#hc-monitoring .mon-tops { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 18px; }
#hc-monitoring .mon-top { padding: 16px 18px; border-radius: 16px; background: #faf9fe; border: 1px solid var(--m-line); }
#hc-monitoring .mon-top h3 { margin-bottom: 14px; }
#hc-monitoring .mon-rank { display: flex; flex-direction: column; gap: 12px; }
#hc-monitoring .mon-rank-row { display: grid; grid-template-columns: 22px 1fr auto; gap: 4px 10px; align-items: center; }
#hc-monitoring .mon-rank-n { width: 22px; height: 22px; border-radius: 7px; display: grid; place-items: center; font-size: 11px; font-weight: 700; background: #ede9fe; color: var(--m-purple); }
#hc-monitoring .mon-rank-row:first-child .mon-rank-n { background: linear-gradient(135deg, #fbbf24, #d99a06); color: #fff; }
#hc-monitoring .mon-rank-name { font-size: 13px; font-weight: 600; color: var(--m-text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
#hc-monitoring .mon-rank-val { font-size: 13px; font-weight: 700; color: var(--m-text); }
#hc-monitoring .mon-rank-bar { grid-column: 2 / 4; height: 6px; background: #efeaf9; border-radius: 3px; overflow: hidden; }
#hc-monitoring .mon-rank-bar div { height: 100%; border-radius: 3px; background: linear-gradient(90deg, var(--m-violet), var(--m-purple)); transition: width .5s; }
#hc-monitoring .mon-top.ram .mon-rank-bar div { background: linear-gradient(90deg, #38bdf8, #0284c7); }
#hc-monitoring .mon-top.dock .mon-rank-bar div { background: linear-gradient(90deg, #34d399, #059669); }

#hc-monitoring .mon-table-wrap { overflow-x: auto; border-radius: 14px; border: 1px solid var(--m-line); }
#hc-monitoring .mon-table { width: 100%; border-collapse: collapse; font-size: 13px; }
#hc-monitoring .mon-table th { text-align: left; padding: 12px 16px; color: var(--m-mute); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: .1em; background: #f7f5fd; }
#hc-monitoring .mon-table td { padding: 12px 16px; border-top: 1px solid var(--m-line); color: #3d3963; }
#hc-monitoring .mon-table tbody tr { transition: background .15s; }
#hc-monitoring .mon-table tbody tr:hover { background: #f7f2fd; }
#hc-monitoring .mon-table td.strong { color: var(--m-text); font-weight: 600; }
#hc-monitoring .mon-table th.sortable { cursor: pointer; user-select: none; }
#hc-monitoring .mon-table th.sortable:hover { color: var(--m-purple); }
#hc-monitoring .mon-na { color: var(--m-mute); }
#hc-monitoring .mon-chip { display: inline-block; background: #ede9fe; color: #5b21b6; border-radius: 20px; padding: 2px 10px; margin: 2px 4px 2px 0; font-size: 12px; }
#hc-monitoring .mon-chip-docker { background: #d1fae5; color: #047857; }
@media (max-width: 640px) {
    #hc-monitoring { padding: 18px 14px 40px; }
    #hc-monitoring .mon-card-body { flex-direction: column; align-items: flex-start; }
}
</style>
