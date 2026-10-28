<template>
    <main v-if="isSuperAdmin" class="server-monitoring">
        <div class="monitoring-heading">
            <div><div class="eyebrow">SUPER ADMIN · INFRASTRUCTURE</div><h1><i class="fa fa-server" aria-hidden="true"></i> Monitoring Serveur</h1><p>État du serveur hôte, des conteneurs et de l’application.</p></div>
            <div class="refresh-status">
                <span class="status-pill" :class="error ? 'status-danger' : paused ? 'status-neutral' : 'status-success'"><span class="status-dot"></span>{{ error ? 'Connexion interrompue' : paused ? 'Actualisation en pause' : loading && !snapshot ? 'Connexion…' : 'Actualisation · 10 s' }}</span>
                <span v-if="lastSuccess" class="muted">Reçu à {{ date(lastSuccess) }}</span>
                <button type="button" class="monitoring-button" :disabled="loading" @click="refresh"><i class="fa fa-refresh" :class="{ 'fa-spin': loading }" aria-hidden="true"></i> Actualiser</button>
            </div>
        </div>

        <div v-if="error" class="notice notice-danger" role="alert">{{ error }} <span v-if="snapshot">Les dernières valeurs reçues sont conservées ; elles peuvent être obsolètes.</span></div>
        <div v-if="!snapshot && loading" class="empty-state"><i class="fa fa-circle-o-notch fa-spin" aria-hidden="true"></i> Chargement des métriques réelles…</div>

        <template v-if="snapshot">
            <section v-if="alerts.length" class="alerts" aria-label="Alertes de monitoring" aria-live="polite">
                <div v-for="alert in alerts" :key="alert.key" class="notice" :class="['critical', 'danger', 'error'].includes(alert.level) ? 'notice-danger' : 'notice-warning'"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> {{ alert.message }}</div>
            </section>
            <div v-else class="healthy-message"><i class="fa fa-check-circle" aria-hidden="true"></i> Aucune alerte active sur les sources disponibles.</div>

            <div class="section-heading"><h2>Serveur hôte</h2><span class="muted">Collecté à {{ date(system.collected_at) }} · {{ hostLabel }}</span></div>
            <div v-if="!host.available" class="notice notice-warning">{{ host.reason || 'Les métriques du serveur hôte sont indisponibles.' }}</div>
            <section class="resource-grid" aria-label="Ressources du serveur hôte">
                <article class="monitoring-card resource-card">
                    <div class="card-title"><h3><i class="fa fa-microchip" aria-hidden="true"></i> CPU</h3><span class="scope-label">HÔTE</span></div>
                    <div class="resource-content"><metric-gauge :value="cpu.usage_percent" label="Utilisation" /><dl><div><dt>Cœurs logiques</dt><dd>{{ number(cpu.cores, 0) }}</dd></div><div><dt>Charge 1 / 5 / 15 min</dt><dd>{{ loads }}</dd></div><div><dt>Uptime</dt><dd>{{ duration(cpu.uptime_seconds) }}</dd></div></dl></div>
                </article>
                <article class="monitoring-card resource-card">
                    <div class="card-title"><h3><i class="fa fa-memory" aria-hidden="true"></i> RAM</h3><span class="scope-label">HÔTE</span></div>
                    <div class="resource-content"><metric-gauge :value="memory.usage_percent" label="Utilisée" /><dl><div><dt>Totale</dt><dd>{{ bytes(memory.total_bytes) }}</dd></div><div><dt>Utilisée</dt><dd>{{ bytes(memory.used_bytes) }}</dd></div><div><dt>Disponible</dt><dd>{{ bytes(memory.available_bytes) }}</dd></div></dl></div>
                </article>
                <article class="monitoring-card resource-card">
                    <div class="card-title"><h3><i class="fa fa-hdd" aria-hidden="true"></i> Disque</h3><span class="scope-label">HÔTE</span></div>
                    <div class="resource-content"><metric-gauge :value="disk.usage_percent" label="Utilisé" /><dl><div><dt>Total</dt><dd>{{ bytes(disk.total_bytes) }}</dd></div><div><dt>Utilisé</dt><dd>{{ bytes(disk.used_bytes) }}</dd></div><div><dt>Disponible</dt><dd>{{ bytes(disk.available_bytes) }}</dd></div></dl></div>
                    <p class="card-note">{{ disk.path ? `Volume mesuré : ${disk.path}` : 'Système de fichiers racine de l’hôte.' }}</p>
                </article>
            </section>

            <section class="chart-grid" aria-label="Évolution des métriques">
                <article class="monitoring-card"><div class="card-title"><h3>Ressources de l’hôte</h3><span class="muted">Dernières 5 minutes ouvertes</span></div><metric-chart label="Évolution CPU, RAM et disque du serveur hôte" :samples="hostSamples" :series="resourceSeries" :maximum="100" unit="%" /></article>
                <article class="monitoring-card"><div class="card-title"><h3>Trafic réseau de l’hôte</h3><span class="muted">Débit mesuré</span></div><metric-chart label="Débits réseau entrant et sortant du serveur hôte" :samples="hostSamples" :series="networkSeries" unit="Mbit/s" /></article>
            </section>

            <section class="monitoring-card network-section">
                <div class="card-title"><h3><i class="fa fa-exchange-alt" aria-hidden="true"></i> Réseau hôte</h3><div class="network-summary"><span>Entrant <strong>{{ rate(network.rx_bytes_per_second) }}</strong></span><span>Sortant <strong>{{ rate(network.tx_bytes_per_second) }}</strong></span></div></div>
                <div v-if="!(network.interfaces || []).length" class="empty-state small">{{ network.reason || 'Interfaces réseau indisponibles.' }}</div>
                <div v-else class="table-scroll"><table><thead><tr><th>Interface</th><th>Entrant</th><th>Sortant</th><th>Capacité du lien</th></tr></thead><tbody><tr v-for="iface in network.interfaces" :key="iface.name"><td><code>{{ iface.name }}</code></td><td>{{ rate(iface.rx_bytes_per_second) }}</td><td>{{ rate(iface.tx_bytes_per_second) }}</td><td>{{ withUnit(iface.speed_mbps, 'Mbit/s') }}</td></tr></tbody></table></div>
                <p class="card-note">La capacité du lien est celle déclarée par l’interface lorsqu’elle est disponible. Elle ne mesure pas le débit maximal de la connexion Internet.</p>
            </section>

            <div class="section-heading"><h2>Requêtes HTTP</h2><span class="muted">{{ http.window_seconds ? `Fenêtre de ${http.window_seconds} secondes` : 'Collecte applicative' }}</span></div>
            <div v-if="!http.available" class="notice notice-warning">{{ http.reason || 'Les métriques HTTP sont indisponibles.' }}</div>
            <section class="kpi-grid" aria-label="Indicateurs HTTP">
                <article v-for="kpi in httpKpis" :key="kpi.label" class="monitoring-card kpi-card"><span>{{ kpi.label }}</span><strong :class="kpi.danger ? 'text-danger' : ''">{{ kpi.value }}</strong><small>{{ kpi.note }}</small></article>
            </section>
            <section class="chart-grid">
                <article class="monitoring-card"><div class="card-title"><h3>Requêtes par seconde</h3></div><metric-chart label="Évolution du nombre de requêtes HTTP par seconde" :samples="samples" :series="requestSeries" unit="req/s" /></article>
                <article class="monitoring-card"><div class="card-title"><h3>Temps de réponse Laravel</h3></div><metric-chart label="Évolution du temps de réponse Laravel moyen et percentile 95" :samples="samples" :series="responseSeries" unit="ms" /></article>
            </section>
            <section class="monitoring-card">
                <div class="card-title"><h3>Endpoints les plus sollicités</h3><span class="muted">{{ withUnit(http.count, 'requêtes', 0) }} sur la fenêtre</span></div>
                <div v-if="!(http.endpoints || []).length" class="empty-state small">Aucune requête observée sur cette fenêtre.</div>
                <div v-else class="table-scroll"><table><thead><tr><th>Méthode</th><th>Endpoint</th><th>Requêtes</th><th>Réponse moyenne</th></tr></thead><tbody><tr v-for="endpoint in http.endpoints" :key="endpoint.method + endpoint.path"><td><span class="method-pill">{{ endpoint.method }}</span></td><td><code>{{ endpoint.path }}</code></td><td>{{ number(endpoint.count, 0) }}</td><td>{{ withUnit(endpoint.average_response_ms, 'ms') }}</td></tr></tbody></table></div>
                <p class="card-note">Requêtes traitées par Laravel, hors monitoring ; les fichiers statiques et les rejets en amont ne sont pas comptés. Collecte locale par tranches de 5 secondes, certaines mesures peuvent être omises en cas de forte contention. Latence aller-retour de cette API depuis votre navigateur : {{ withUnit(roundTripMs, 'ms') }}.</p>
            </section>

            <div class="section-heading"><h2>Conteneurs Docker</h2><span class="muted">{{ docker.available ? `${(docker.containers || []).length} conteneurs` : 'Collecteur Docker' }}</span></div>
            <section class="monitoring-card">
                <div v-if="!docker.available" class="notice notice-warning">{{ docker.reason || 'Le service Docker est inaccessible au collecteur.' }}</div>
                <div v-if="docker.available && docker.reason" class="notice notice-warning">{{ docker.reason }}</div>
                <div v-if="docker.truncated" class="notice notice-warning">Liste limitée à {{ (docker.containers || []).length }} conteneurs sur {{ docker.total_containers }}.</div>
                <div v-if="docker.available && !(docker.containers || []).length" class="empty-state small">Aucun conteneur observé.</div>
                <div v-else-if="docker.available" class="table-scroll"><table><thead><tr><th>Conteneur</th><th>État</th><th>Statut</th><th>CPU</th><th>RAM utilisée / limite</th><th>RAM %</th></tr></thead><tbody><tr v-for="container in docker.containers" :key="container.id"><td><strong>{{ container.name }}</strong><small class="container-id">{{ (container.id || '').slice(0, 12) }}</small></td><td><span class="status-pill" :class="container.state === 'running' ? 'status-success' : container.state === 'exited' ? 'status-neutral' : 'status-danger'">{{ container.state }}</span></td><td>{{ container.status }}</td><td>{{ withUnit(container.cpu_percent, '%') }}</td><td>{{ bytes(container.memory_bytes) }} / {{ bytes(container.memory_limit_bytes) }}</td><td>{{ withUnit(container.memory_percent, '%') }}</td></tr></tbody></table></div>
                <p class="card-note">Les ressources des conteneurs sont distinctes de celles de l’hôte. Pour Docker, 100 % de CPU correspond à un cœur logique.</p>
            </section>

            <div class="section-heading"><h2>Services et application Laravel</h2></div>
            <section class="service-grid">
                <article class="monitoring-card">
                    <div class="card-title"><h3><i class="fa fa-layer-group" aria-hidden="true"></i> Redis</h3><span class="status-pill" :class="redis.available ? 'status-success' : 'status-danger'">{{ redis.available ? 'Disponible' : 'Indisponible' }}</span></div>
                    <p v-if="!redis.available" class="unavailable-message">{{ redis.reason || 'Connexion au service impossible.' }}</p>
                    <dl class="service-details"><div><dt>Mémoire consommée</dt><dd>{{ bytes(redis.used_memory_bytes) }}</dd></div><div><dt>Limite de mémoire</dt><dd>{{ redis.maxmemory_bytes === 0 ? 'Sans limite configurée' : bytes(redis.maxmemory_bytes) }}</dd></div><div><dt>Latence du service</dt><dd>{{ withUnit(redis.latency_ms, 'ms') }}</dd></div></dl>
                </article>
                <article class="monitoring-card">
                    <div class="card-title"><h3><i class="fa fa-tasks" aria-hidden="true"></i> Jobs échoués</h3><strong class="failure-count">{{ failedJobs.available ? number(failedJobs.count, 0) : '—' }}</strong></div>
                    <p class="card-note">Échecs observés sur les dernières 24 heures. {{ failedJobs.since ? `Suivi actif depuis le ${date(failedJobs.since, true)} ; l’historique antérieur n’est pas inclus.` : '' }}</p>
                    <p v-if="!failedJobs.available" class="unavailable-message">{{ failedJobs.reason || 'Suivi des jobs indisponible.' }}</p>
                    <div v-else-if="!(failedJobs.entries || []).length" class="empty-state small">Aucun job échoué observé.</div>
                    <ul v-else class="failure-list"><li v-for="(job, index) in failedJobs.entries" :key="index"><div><strong>{{ job.queue || 'File inconnue' }}</strong><span class="muted">{{ date(job.timestamp, true) }}</span></div><small>{{ job.connection }}</small><pre>{{ job.exception }}</pre></li></ul>
                </article>
            </section>
            <section class="monitoring-card logs-section">
                <div class="card-title"><h3><i class="fa fa-file-alt" aria-hidden="true"></i> Logs, erreurs et exceptions</h3><span class="muted">Dernières entrées disponibles</span></div>
                <p class="card-note" style="margin: 0 0 12px">Jusqu’à 50 entrées récentes des fichiers de logs actifs. Les messages et les contextes sont masqués ; les classes d’exception sont conservées.</p>
                <p v-if="!logs.available" class="unavailable-message">{{ logs.reason || 'Les logs Laravel sont indisponibles.' }}</p>
                <div v-else-if="!(logs.entries || []).length" class="empty-state small">Aucune entrée récente.</div>
                <div v-else class="log-list"><div v-for="(entry, index) in logs.entries" :key="index" class="log-entry"><div class="log-metadata"><span class="log-level" :class="['error', 'critical', 'alert', 'emergency'].includes((entry.level || '').toLowerCase()) ? 'text-danger' : ''">{{ entry.level }}</span><time>{{ date(entry.timestamp, true) }}</time></div><pre>{{ entry.message }}</pre></div></div>
            </section>
            <p class="monitoring-footer">Les valeurs « — » signalent une mesure indisponible ou en attente du prochain échantillon. Les graphiques conservent jusqu’à 5 minutes de mesures pendant l’ouverture de cette page.</p>
        </template>
    </main>
</template>

<script>
import axios from "axios";
import MetricChart from "./MetricChart.vue";
import MetricGauge from "./MetricGauge.vue";

const validNumber = value => typeof value === "number" && Number.isFinite(value);

export default {
    components: { MetricChart, MetricGauge },
    data: () => ({
        snapshot: null, samples: [], accessDenied: false, loading: false, error: "", timer: null, controller: null,
        destroyed: false, paused: document.hidden, lastSuccess: null, roundTripMs: null,
        resourceSeries: [{ key: "cpu", label: "CPU", color: "#7939b8" }, { key: "memory", label: "RAM", color: "#149bc0" }, { key: "disk", label: "Disque", color: "#df9c23" }],
        networkSeries: [{ key: "rx", label: "Entrant", color: "#149bc0" }, { key: "tx", label: "Sortant", color: "#7939b8" }],
        requestSeries: [{ key: "rps", label: "Requêtes", color: "#7939b8" }],
        responseSeries: [{ key: "response", label: "Moyenne", color: "#149bc0" }, { key: "p95", label: "P95 approximatif", color: "#df9c23" }],
    }),
    computed: {
        isSuperAdmin() { return !!this.$store.state.auth.user?.is_super_admin; },
        system() { return this.snapshot?.system || {}; },
        host() { return this.system.host || {}; },
        hostSamples() {
            const unique = new Map();
            for (const sample of this.samples) {
                if (sample.systemTime && new Date(sample.systemTime).getTime() >= Date.now() - 300000) {
                    unique.set(sample.systemTime, { ...sample, time: sample.systemTime });
                }
            }
            return Array.from(unique.values());
        },
        hostLabel() { return this.host.available ? 'Ressources physiques / VM' : 'Source indisponible'; },
        cpu() { return this.host.cpu || {}; },
        memory() { return this.host.memory || {}; },
        disk() { return this.host.disk || {}; },
        network() { return this.host.network || {}; },
        docker() { return this.system.docker || {}; },
        http() { return this.snapshot?.http || {}; },
        redis() { return this.snapshot?.redis || {}; },
        logs() { return this.snapshot?.laravel?.logs || {}; },
        failedJobs() { return this.snapshot?.laravel?.failed_jobs || {}; },
        alerts() { return this.snapshot?.alerts || []; },
        loads() { return Array.isArray(this.cpu.load) ? this.cpu.load.map(value => this.number(value)).join(' / ') : '—'; },
        httpKpis() {
            const http = this.http;
            return [
                { label: 'Requêtes / seconde', value: this.withUnit(http.requests_per_second, 'req/s'), note: 'Moyenne sur la fenêtre' },
                { label: 'Temps de réponse', value: this.withUnit(http.average_response_ms, 'ms'), note: 'Traitement Laravel moyen' },
                { label: http.p95_approximate ? 'Latence P95 approximative' : 'Latence P95', value: http.p95_overflow ? '> 30 000 ms' : this.withUnit(http.p95_response_ms, 'ms'), note: http.p95_approximate ? 'Borne haute estimée par histogramme' : '95 % des réponses sont plus rapides' },
                { label: 'Erreurs 4xx', value: this.number(http.errors_4xx, 0), note: 'Sur la fenêtre', danger: http.errors_4xx > 0 },
                { label: 'Erreurs 5xx', value: this.number(http.errors_5xx, 0), note: 'Sur la fenêtre', danger: http.errors_5xx > 0 },
            ];
        },
    },
    watch: {
        isSuperAdmin(allowed) { if (!allowed) { this.stop(); this.snapshot = null; this.samples = []; } },
    },
    mounted() {
        document.addEventListener('visibilitychange', this.visibilityChanged);
        if (!document.hidden && this.isSuperAdmin) this.refresh();
    },
    beforeUnmount() {
        this.destroyed = true;
        this.stop();
        document.removeEventListener('visibilitychange', this.visibilityChanged);
    },
    methods: {
        stop() { clearTimeout(this.timer); this.timer = null; if (this.controller) this.controller.abort(); },
        visibilityChanged() {
            this.paused = document.hidden;
            if (document.hidden) this.stop();
            else if (this.isSuperAdmin && !this.loading) this.refresh();
        },
        async refresh() {
            if (this.loading || this.destroyed || this.accessDenied || !this.isSuperAdmin) return;
            clearTimeout(this.timer);
            this.loading = true;
            this.controller = new AbortController();
            const started = performance.now();
            try {
                const { data } = await axios.get('/api/server-monitoring', { signal: this.controller.signal, timeout: 10000 });
                if (this.destroyed || this.controller.signal.aborted || !this.isSuperAdmin) return;
                this.roundTripMs = performance.now() - started;
                this.snapshot = data;
                this.lastSuccess = new Date().toISOString();
                this.error = '';
                const sample = {
                    time: data.collected_at || this.lastSuccess,
                    systemTime: this.host.available && this.system.collected_at ? this.system.collected_at : data.collected_at || this.lastSuccess,
                    cpu: this.host.available ? this.cpu.usage_percent : null,
                    memory: this.host.available ? this.memory.usage_percent : null,
                    disk: this.host.available ? this.disk.usage_percent : null,
                    rx: this.host.available && validNumber(this.network.rx_bytes_per_second) ? this.network.rx_bytes_per_second * 8 / 1000000 : null,
                    tx: this.host.available && validNumber(this.network.tx_bytes_per_second) ? this.network.tx_bytes_per_second * 8 / 1000000 : null,
                    rps: this.http.available ? this.http.requests_per_second : null,
                    response: this.http.available ? this.http.average_response_ms : null,
                    p95: this.http.available ? this.http.p95_response_ms : null,
                };
                const cutoff = Date.now() - 300000;
                this.samples = [...this.samples.filter(point => new Date(point.time).getTime() >= cutoff), sample].slice(-61);
            } catch (error) {
                if (axios.isCancel(error) || this.destroyed) return;
                if ([401, 403].includes(error.response?.status)) {
                    this.accessDenied = true;
                    this.snapshot = null;
                    this.samples = [];
                    this.$router.replace({ name: 'dashboard' });
                    return;
                }
                this.error = 'Impossible d’actualiser le monitoring. Une nouvelle tentative sera effectuée automatiquement.';
            } finally {
                this.loading = false;
                this.controller = null;
                if (!this.destroyed && !this.accessDenied && !document.hidden && this.isSuperAdmin) {
                    this.timer = setTimeout(this.refresh, Math.max(0, 10000 - (performance.now() - started)));
                }
            }
        },
        number(value, digits = 1) { return validNumber(value) ? value.toLocaleString('fr-FR', { maximumFractionDigits: digits }) : '—'; },
        withUnit(value, unit, digits = 1) { return validNumber(value) ? `${this.number(value, digits)} ${unit}` : '—'; },
        bytes(value) {
            if (!validNumber(value) || value < 0) return '—';
            if (value === 0) return '0 o';
            const units = ['o', 'Kio', 'Mio', 'Gio', 'Tio'];
            const index = Math.max(0, Math.min(Math.floor(Math.log(value) / Math.log(1024)), units.length - 1));
            return `${this.number(value / Math.pow(1024, index))} ${units[index]}`;
        },
        rate(value) { return validNumber(value) ? `${this.bytes(value)}/s` : '—'; },
        duration(value) {
            if (!validNumber(value)) return '—';
            const days = Math.floor(value / 86400);
            const hours = Math.floor((value % 86400) / 3600);
            const minutes = Math.floor((value % 3600) / 60);
            return `${days ? days + ' j ' : ''}${hours} h ${minutes} min`;
        },
        date(value, full = false) {
            if (!value) return '—';
            const parsed = new Date(value);
            return Number.isNaN(parsed.getTime()) ? String(value) : full ? parsed.toLocaleString('fr-FR') : parsed.toLocaleTimeString('fr-FR');
        },
    },
};
</script>

<style scoped>
.server-monitoring { height: 100%; overflow: auto; background: #f4f6fb; padding: 26px clamp(14px, 3vw, 42px) 30px; color: #26334e; }
.monitoring-heading { display: flex; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 23px; }
.eyebrow { font-size: 10px; font-weight: 700; letter-spacing: 1.5px; color: #7939b8; margin-bottom: 7px; }
h1 { font-size: 25px; font-weight: 750; margin: 0 0 8px; color: #26334e; }
h1 i { font-size: 22px; color: #7939b8; margin-right: 6px; }
.monitoring-heading p { margin: 0; color: #778298; font-size: 13px; }
.refresh-status { display: flex; flex-wrap: wrap; justify-content: flex-end; align-items: center; gap: 10px; }
.monitoring-button { color: #7939b8; border: 1px solid #ded3ef; background: white; border-radius: 8px; padding: 8px 12px; font-size: 12px; cursor: pointer; }
.monitoring-button:disabled { opacity: .6; cursor: wait; }
.monitoring-button i { margin-right: 5px; }
.status-pill { display: inline-flex; align-items: center; white-space: nowrap; font-size: 11px; font-weight: 650; gap: 6px; padding: 5px 9px; border-radius: 20px; }
.status-success { color: #16795a; background: #e6f6ee; }
.status-danger { color: #b12e4a; background: #fde9ed; }
.status-neutral { color: #6c778c; background: #edf0f6; }
.status-dot { background: currentColor; width: 6px; height: 6px; border-radius: 50%; }
.muted { color: #7c879b; font-size: 11px; font-weight: 400; }
.section-heading { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; margin: 25px 0 12px; }
.section-heading h2 { margin: 0; font-size: 17px; font-weight: 700; }
.monitoring-card { background: white; border-radius: 13px; padding: 19px; border: 1px solid #e7ebf3; box-shadow: 0 3px 10px #26334e04; min-width: 0; }
.card-title { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 18px; }
.card-title h3 { font-size: 13px; font-weight: 700; margin: 0; }
.card-title h3 i { color: #8b70a8; margin-right: 6px; }
.scope-label { color: #9b8bac; font-size: 9px; font-weight: 700; letter-spacing: 1px; }
.resource-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 15px; }
.resource-content { display: flex; align-items: center; gap: 20px; }
dl { margin: 0; flex: 1; min-width: 0; }
dl > div { display: flex; flex-wrap: wrap; align-items: baseline; gap: 3px 8px; justify-content: space-between; padding: 5px 0; }
dt { font-weight: 400; color: #7c879b; font-size: 11px; }
dd { margin: 0; color: #34435d; font-size: 12px; font-weight: 650; }
.card-note { color: #8a93a5; font-size: 11px; line-height: 1.6; margin: 14px 0 0; }
.chart-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 15px; margin: 15px 0; }
.chart-grid .card-title { margin-bottom: 8px; }
.notice { border-radius: 9px; padding: 11px 14px; font-size: 12px; line-height: 1.6; margin-bottom: 9px; }
.notice i { margin-right: 5px; }
.notice-danger { background: #fde9ed; color: #a3334c; border: 1px solid #f2d2da; }
.notice-warning { background: #fff6df; color: #917022; border: 1px solid #f2e5c3; }
.healthy-message { font-size: 12px; color: #40846e; margin-bottom: 10px; }
.healthy-message i { margin-right: 5px; }
.empty-state { text-align: center; padding: 50px 15px; font-size: 13px; color: #8a93a5; }
.empty-state i { margin-right: 8px; }
.empty-state.small { padding: 22px 12px; font-size: 12px; }
.network-summary { display: flex; flex-wrap: wrap; gap: 18px; font-size: 11px; color: #7c879b; }
.network-summary strong { margin-left: 5px; color: #34435d; }
.table-scroll { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 12px; text-align: left; }
th { font-size: 10px; color: #8a93a5; font-weight: 650; text-transform: uppercase; letter-spacing: .4px; padding: 9px 12px; background: #f8f9fc; white-space: nowrap; }
td { padding: 12px; border-bottom: 1px solid #eff1f6; white-space: nowrap; }
tr:last-child td { border-bottom: 0; }
code { font-size: 11px; color: #6e5090; }
.container-id { display: block; color: #98a0b1; margin-top: 4px; font-size: 10px; }
.method-pill { font-size: 10px; background: #f1ebf8; color: #7939b8; padding: 4px 6px; border-radius: 4px; }
.kpi-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 13px; }
.kpi-card { display: flex; flex-direction: column; gap: 8px; }
.kpi-card > span { font-size: 11px; color: #7c879b; }
.kpi-card > strong { font-size: 25px; color: #34435d; line-height: 1.2; }
.kpi-card > small { font-size: 10px; color: #9ba2b1; }
.text-danger { color: #c33b54 !important; }
.service-grid { display: grid; grid-template-columns: minmax(240px, .8fr) minmax(0, 1.2fr); gap: 15px; margin-bottom: 15px; }
.service-details > div { border-bottom: 1px solid #eff1f6; padding: 12px 0; }
.service-details > div:last-child { border: 0; }
.failure-count { color: #7939b8; font-size: 25px; }
.unavailable-message { color: #a17a32; font-size: 12px; line-height: 1.6; }
.failure-list { list-style: none; padding: 0; margin: 12px 0 0; max-height: 230px; overflow: auto; }
.failure-list li { border-top: 1px solid #eff1f6; padding: 12px 0; font-size: 12px; }
.failure-list li > div { display: flex; justify-content: space-between; gap: 10px; }
.failure-list small { color: #8a93a5; }
pre { white-space: pre-wrap; overflow-wrap: anywhere; font-size: 11px; line-height: 1.6; margin: 7px 0 0; font-family: monospace; }
.log-list { max-height: 380px; overflow: auto; background: #f9fafd; border-radius: 8px; padding: 0 14px; }
.log-entry { padding: 12px 0; border-bottom: 1px solid #e9edf5; }
.log-entry:last-child { border: 0; }
.log-metadata { display: flex; align-items: center; gap: 12px; font-size: 10px; color: #8a93a5; }
.log-level { font-weight: 700; text-transform: uppercase; color: #6c778c; }
.log-entry pre { color: #536079; }
.monitoring-footer { color: #969fb1; font-size: 11px; line-height: 1.7; margin: 18px 0 0; }
@media (min-width: 1800px) { .server-monitoring { padding-left: max(30px, calc((100vw - 1700px) / 2)); padding-right: max(30px, calc((100vw - 1700px) / 2)); } }
@media (max-width: 1200px) { .resource-content { gap: 12px; flex-wrap: wrap; } .resource-content dl { min-width: 130px; } .kpi-card > strong { font-size: 20px; } }
@media (max-width: 900px) { .monitoring-heading { align-items: flex-start; flex-direction: column; } .refresh-status { justify-content: flex-start; } .resource-grid { grid-template-columns: 1fr; } .resource-content { flex-wrap: nowrap; gap: 24px; } .resource-content dl { flex: 1; } .chart-grid, .service-grid { grid-template-columns: 1fr; } .kpi-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 500px) { .server-monitoring { padding: 18px 12px; } h1 { font-size: 22px; } .monitoring-card { padding: 15px; } .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .section-heading { align-items: flex-start; flex-direction: column; gap: 5px; } .card-title { flex-wrap: wrap; } .network-summary { gap: 12px; } .resource-content { gap: 16px; } }
</style>
