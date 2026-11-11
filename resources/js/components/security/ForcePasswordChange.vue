<template>
    <div
        v-if="$store.state.auth.user.must_change_password"
        class="force-password-change"
    >
        <div class="force-password-card">
            <img src="/images/logo.png" alt="Heroes CRM" class="logo" />
            <h2>Choisissez votre mot de passe</h2>

            <div class="field-group">
                <label>Mot de passe actuel</label>
                <div class="password-input">
                    <input
                        v-model="form.current_password"
                        :type="showPasswords.current_password ? 'text' : 'password'"
                        autocomplete="current-password"
                        placeholder="Mot de passe actuel"
                    />
                    <button
                        type="button"
                        class="toggle-password"
                        :aria-label="showPasswords.current_password ? 'Masquer le mot de passe actuel' : 'Afficher le mot de passe actuel'"
                        @click="showPasswords.current_password = !showPasswords.current_password"
                    >
                        <i
                            :class="showPasswords.current_password ? 'fa fa-eye-slash' : 'fa fa-eye'"
                            aria-hidden="true"
                        ></i>
                    </button>
                </div>
            </div>

            <div class="field-group">
                <label>Nouveau mot de passe</label>
                <div class="password-input">
                    <input
                        v-model="form.password"
                        :type="showPasswords.password ? 'text' : 'password'"
                        autocomplete="new-password"
                        placeholder="Nouveau mot de passe"
                    />
                    <button
                        type="button"
                        class="toggle-password"
                        :aria-label="showPasswords.password ? 'Masquer le nouveau mot de passe' : 'Afficher le nouveau mot de passe'"
                        @click="showPasswords.password = !showPasswords.password"
                    >
                        <i
                            :class="showPasswords.password ? 'fa fa-eye-slash' : 'fa fa-eye'"
                            aria-hidden="true"
                        ></i>
                    </button>
                </div>
            </div>

            <PasswordRules :password="form.password" />

            <div class="field-group">
                <label>Confirmation</label>
                <div class="password-input">
                    <input
                        v-model="form.password_confirmation"
                        :type="showPasswords.password_confirmation ? 'text' : 'password'"
                        autocomplete="new-password"
                        placeholder="Confirmez le mot de passe"
                    />
                    <button
                        type="button"
                        class="toggle-password"
                        :aria-label="showPasswords.password_confirmation ? 'Masquer la confirmation du mot de passe' : 'Afficher la confirmation du mot de passe'"
                        @click="showPasswords.password_confirmation = !showPasswords.password_confirmation"
                    >
                        <i
                            :class="showPasswords.password_confirmation ? 'fa fa-eye-slash' : 'fa fa-eye'"
                            aria-hidden="true"
                        ></i>
                    </button>
                </div>
            </div>

            <div v-if="serverErrors.length" class="errors">
                <ul>
                    <li v-for="(error, index) in serverErrors" :key="index">
                        {{ error }}
                    </li>
                </ul>
            </div>

            <button @click.prevent="submit" :disabled="loading">
                {{ loading ? "Enregistrement..." : "Enregistrer" }}
            </button>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import PasswordRules from "./PasswordRules.vue";

export default {
    name: "ForcePasswordChange",
    components: { PasswordRules },
    data() {
        return {
            loading: false,
            serverErrors: [],
            showPasswords: {
                current_password: false,
                password: false,
                password_confirmation: false,
            },
            form: {
                current_password: "",
                password: "",
                password_confirmation: "",
            },
        };
    },
    methods: {
        async submit() {
            this.serverErrors = [];
            this.loading = true;

            try {
                const { data } = await axios.post("/api/password/change", this.form);

                if (data.success) {
                    await this.$store.dispatch("auth/user");

                    if (!this.$store.state.auth.authenticated) {
                        this.serverErrors = [
                            "Le mot de passe a été modifié, mais la session n’a pas pu être actualisée. Veuillez vous reconnecter.",
                        ];
                        return;
                    }

                    if (this.$store.state.auth.user.must_change_password !== false) {
                        this.serverErrors = [
                            "Le changement de mot de passe n’a pas été confirmé. Veuillez réessayer.",
                        ];
                        return;
                    }

                    this.$router.push({ name: "dashboard" });
                    return;
                }

                this.serverErrors = [data.message || "Erreur lors du changement de mot de passe."];
            } catch (error) {
                const responseErrors = error?.response?.data?.errors;

                if (responseErrors) {
                    this.serverErrors = Object.values(responseErrors).flat();
                } else {
                    this.serverErrors = [
                        error?.response?.data?.message ||
                            "Erreur lors du changement de mot de passe.",
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
.force-password-change {
    position: fixed;
    inset: 0;
    z-index: 99999;
    background-color: #f6f4f9;
    background-image: url("/images/bg.jpg");
    background-size: cover;
    background-position: center;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.force-password-card {
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

.field-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 1rem;
}

label {
    margin-bottom: 0.35rem;
    color: #212529;
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

button {
    width: 100%;
    border: none;
    border-radius: 5px;
    height: 40px;
    background: linear-gradient(121deg, #7939b8 0%, #7a59d8 100%);
    color: white;
    font-weight: 700;
    cursor: pointer;
}

button:disabled {
    opacity: 0.7;
    cursor: wait;
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
