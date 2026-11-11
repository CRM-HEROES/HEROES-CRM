<template>
    <div class="invitation-wrapper">
        <div class="invitation-card">
            <img src="/images/logo.png" alt="Heroes CRM" class="logo" />

            <template v-if="done">
                <h2>Compte activé</h2>
                <p class="info">
                    Votre mot de passe est enregistré. Vous pouvez maintenant
                    vous connecter avec votre adresse email.
                </p>
                <router-link :to="{ name: 'login' }" class="primary">
                    Se connecter
                </router-link>
            </template>

            <template v-else-if="invalid">
                <h2>Lien non valable</h2>
                <p class="info">
                    {{ invalidMessage }}
                </p>
            </template>

            <form v-else action="javascript:void(0)" @submit.prevent="submit">
                <h2>Choisissez votre mot de passe</h2>

                <div class="field-group">
                    <label for="invitation-password">Mot de passe</label>
                    <div class="password-input">
                        <input
                            id="invitation-password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            autocomplete="new-password"
                            placeholder="Nouveau mot de passe"
                        />
                        <button
                            type="button"
                            class="toggle-password"
                            :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                            @click="showPassword = !showPassword"
                        >
                            <i
                                :class="showPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"
                                aria-hidden="true"
                            ></i>
                        </button>
                    </div>
                </div>

                <PasswordRules :password="form.password" />

                <div class="field-group">
                    <label for="invitation-confirmation">Confirmation</label>
                    <div class="password-input">
                        <input
                            id="invitation-confirmation"
                            v-model="form.password_confirmation"
                            :type="showPassword ? 'text' : 'password'"
                            autocomplete="new-password"
                            placeholder="Confirmez le mot de passe"
                        />
                    </div>
                    <small v-if="mismatch" class="mismatch">
                        Les deux mots de passe ne sont pas identiques.
                    </small>
                </div>

                <div v-if="serverErrors.length" class="errors">
                    <ul>
                        <li v-for="(error, index) in serverErrors" :key="index">
                            {{ error }}
                        </li>
                    </ul>
                </div>

                <button
                    type="submit"
                    class="primary"
                    :disabled="loading || !canSubmit"
                >
                    {{ loading ? "Enregistrement..." : "Activer mon compte" }}
                </button>
            </form>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import PasswordRules from "../security/PasswordRules.vue";
import { isStrongPassword } from "../security/passwordRules";

export default {
    name: "Invitation",

    components: { PasswordRules },

    data() {
        return {
            loading: false,
            done: false,
            invalid: false,
            invalidMessage: "",
            showPassword: false,
            serverErrors: [],
            form: {
                password: "",
                password_confirmation: "",
            },
        };
    },

    computed: {
        mismatch() {
            return (
                this.form.password_confirmation.length > 0 &&
                this.form.password !== this.form.password_confirmation
            );
        },

        canSubmit() {
            return (
                isStrongPassword(this.form.password) &&
                this.form.password === this.form.password_confirmation
            );
        },
    },

    created() {
        // Le lien de l'email doit contenir la signature ; sans elle inutile d'afficher le formulaire
        const query = new URLSearchParams(window.location.search);

        if (!query.get("signature") || !query.get("expires")) {
            this.invalid = true;
            this.invalidMessage =
                "Ce lien est incomplet. Utilisez le lien reçu par email ou demandez une nouvelle invitation à votre administrateur.";
        }
    },

    methods: {
        async submit() {
            if (!this.canSubmit) {
                return;
            }

            this.serverErrors = [];
            this.loading = true;

            try {
                await axios.get("/sanctum/csrf-cookie");

                // La chaîne de paramètres du lien est la signature de la route : on la renvoie telle quelle
                await axios.post(
                    `/api/invitation/${this.$route.params.user}${window.location.search}`,
                    this.form
                );

                this.done = true;
            } catch (error) {
                const response = error && error.response;

                if (response && response.status === 410) {
                    this.invalid = true;
                    this.invalidMessage = response.data.message;
                } else if (response && response.status === 403) {
                    this.invalid = true;
                    this.invalidMessage =
                        "Ce lien n'est pas valide ou a expiré. Demandez une nouvelle invitation à votre administrateur.";
                } else if (response && response.data && response.data.errors) {
                    this.serverErrors = Object.values(response.data.errors).flat();
                } else {
                    this.serverErrors = [
                        (response && response.data && response.data.message) ||
                            "Erreur lors de l'activation du compte.",
                    ];
                }
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<style scoped>
.invitation-wrapper {
    width: 100%;
    min-height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background-color: #f6f4f9;
    background-image: url("/images/bg.jpg");
    background-size: cover;
    background-position: center;
}

.invitation-card {
    width: min(420px, 92vw);
    background: #fff8;
    border-radius: 10px;
    padding: 30px;
    color: #212529;
}

.logo {
    display: block;
    width: 140px;
    margin: 0 auto 1rem;
}

h2 {
    text-align: center;
    margin-bottom: 1.5rem;
    color: #212529;
}

.info {
    text-align: center;
    margin-bottom: 1.5rem;
}

.field-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 1rem;
}

label {
    margin-bottom: 0.35rem;
    font-size: 0.92rem;
}

.password-input {
    position: relative;
}

.password-input input {
    width: 100%;
    height: 40px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background: #fff;
    color: #212529;
    padding: 0 40px 0 10px;
}

.toggle-password {
    position: absolute;
    top: 0;
    right: 0;
    width: 40px;
    height: 40px;
    padding: 0;
    border: 0;
    background: transparent;
    color: #555;
    cursor: pointer;
}

.mismatch {
    margin-top: 0.3rem;
    color: #b42318;
}

.primary {
    display: block;
    width: 100%;
    border: none;
    border-radius: 5px;
    height: 40px;
    line-height: 40px;
    text-align: center;
    background: linear-gradient(121deg, #7939b8 0%, #7a59d8 100%);
    color: white;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
}

.primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.errors {
    margin-bottom: 1rem;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    background: rgba(127, 29, 29, 0.45);
    color: #fecaca;
}

.errors ul {
    margin: 0;
    padding-left: 1.2rem;
}
</style>
