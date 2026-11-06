<template>
    <div v-if="alerts.length" id="hc-login-location-banner">
        <div class="hc-llb-icon">⚠️</div>
        <div class="hc-llb-content">
            <strong>Nouvelle connexion détectée</strong>
            <p v-for="alert in alerts" :key="alert.id">
                Votre compte a été utilisé depuis
                <b>{{ place(alert) }}</b> (IP {{ alert.ip }}).
            </p>
            <p class="hc-llb-help">
                Si c'était vous, confirmez avec le lien reçu par email.
                Sinon, déconnectez immédiatement votre compte.
            </p>
            <div class="hc-llb-buttons">
                <button :disabled="sending" @click="report">
                    {{ sending ? "..." : "Ce n'était pas moi" }}
                </button>
                <button class="hc-llb-secondary" @click="alerts = []">
                    Fermer
                </button>
            </div>
        </div>
    </div>
</template>

<style>
#hc-login-location-banner {
    position: fixed;
    top: 70px;
    right: 20px;
    z-index: 9999;
    width: 360px;
    max-width: 90%;
    display: flex;
    gap: 12px;
    padding: 16px;
    border-radius: 10px;
    background: #fdf4e6;
    color: #8b4a15;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
}
.hc-llb-icon {
    font-size: 28px;
}
.hc-llb-content p {
    margin: 6px 0;
}
.hc-llb-help {
    font-size: 13px;
}
.hc-llb-buttons {
    display: flex;
    gap: 8px;
    margin-top: 10px;
}
.hc-llb-buttons button {
    border: none;
    border-radius: 5px;
    padding: 8px 14px;
    color: white;
    cursor: pointer;
    background: #c0392b;
}
.hc-llb-buttons button.hc-llb-secondary {
    background: #999;
}
</style>

<script>
import { mapActions } from "vuex";

export default {
    name: "login-location-banner",

    data() {
        return {
            alerts: [],
            sending: false,
        };
    },

    mounted() {
        this.fetchAlerts();
    },

    methods: {
        ...mapActions({
            signOut: "auth/logout",
        }),

        async fetchAlerts() {
            try {
                const { data } = await axios.get("/api/login-location/pending");
                this.alerts = data;
            } catch (e) {
                // Une erreur ici ne doit jamais gêner l'utilisation du CRM
            }
        },

        place(alert) {
            return alert.city
                ? alert.city + ", " + alert.country
                : alert.country;
        },

        async report() {
            this.sending = true;

            try {
                for (const alert of this.alerts) {
                    await axios.post(
                        "/api/login-location/" + alert.id + "/report"
                    );
                }
            } catch (e) {
                // Le compte est peut-être déjà déconnecté : on continue
            }

            this.sending = false;
            this.signOut();
            this.$router.push({ name: "login" });
        },
    },
};
</script>
