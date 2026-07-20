<template>
    <div id="hc-login-wrapper">
        <div id="hc-login">
            <form
                class="hc-login-bloc"
                action="javascript:void(0)"
                method="post"
            >
                <img id="hc-login-form-logo" src="/images/logo.png" />
                <h1
                    id="hc-login-form-title"
                    v-text="$t('auth.login.title')"
                ></h1>
                <div id="hc-login-form-content">
                    <div id="hc-login-form-inputs">
                        <label>
                            <input
                                type="email"
                                id="hc-login-form-input-login"
                                :placeholder="$t('auth.email')"
                                v-model="auth.email"
                                name="email"
                            />
                            <email-autocompletion
                                style="top: 36px"
                                v-model="auth.email"
                            />
                        </label>
                        <label>
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                v-model="auth.password"
                                name="password"
                                id="hc-login-form-input-password"
                                :placeholder="$t('auth.password')"
                            />
                            <icon
                                @click.prevent="showPassword = !showPassword"
                                tag="a"
                                :class="
                                    showPassword
                                        ? 'fa fa-eye-slash'
                                        : 'fa fa-eye'
                                "
                                :size="40"
                                style="position: absolute; top: 0; right: 0"
                            />
                        </label>
                    </div>
                    <button
                        id="hc-login-form-submit"
                        type="submit"
                        :disabled="processing"
                        @click="login"
                    >
                        {{
                            processing
                                ? $t("auth.login.authenticating")
                                : $t("auth.login.authenticate")
                        }}
                    </button>
                    <button
                        @click.prevent.stop="google"
                        id="hc-login-google"
                        v-text="$t('auth.login.google')"
                    ></button>
                    <div id="hc-login-forgotten">
                        <router-link
                            :to="{ name: 'password.reset' }"
                            v-text="$t('auth.login.forget_password')"
                        ></router-link>
                    </div>
                    <div id="hc-login-parteners"></div>
                </div>
                <div id="hc-login-form-other">
                    <div id="hc-login-sign-in">
                        <router-link
                            :to="{ name: 'register' }"
                            v-text="$t('auth.login.sign_up')"
                        ></router-link>
                    </div>
                </div>
            </form>

            <div class="hc-login-bloc">
                <ul id="hc-auth-infos-check">
                    <li v-html="$t('auth.info_check.1')"></li>
                    <li v-html="$t('auth.info_check.2')"></li>
                    <li v-html="$t('auth.info_check.3')"></li>
                    <li
                        v-html="$t('auth.info_check.4')"
                        id="hc-auth-team-service"
                    ></li>
                </ul>
            </div>

            <div class="hc-login-bloc" id="auth-external-partner">
                <span v-text="$t('auth.partner.title')"></span>
                <span>Google</span>
                <ul>
                    <li>
                        <img
                            src="images/partenaire-ext/google-auth.png"
                            title="Google Authenticator"
                            alt="Google Authenticator"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/google-map.png"
                            title="Google Map"
                            alt="Google Map"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/google-calendar.png"
                            title="Google Calendar"
                            alt="Google Calendar"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/google-sheet.png"
                            title="Google Sheet"
                            alt="Google Sheet"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/google-contact.png"
                            title="Google Contact"
                            alt="Google Contact"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/google-message.png"
                            title="Google Message"
                            alt="Google Message"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/google-avis.png"
                            title="Google Avis"
                            alt="Google Avis"
                        />
                    </li>
                </ul>

                <span v-text="$t('auth.partner.call_sms_email')"></span>
                <ul>
                    <li>
                        <img
                            src="images/partenaire-ext/aircall.png"
                            title="Aircall"
                            alt="Aircall"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/kavkom.png"
                            title="Kavkom"
                            alt="Kavkom"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/whatsapp.png"
                            title="Whatsapp"
                            alt="Whatsapp"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/smsbox.png"
                            title="SMSBOX"
                            alt="SMSBOX"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/sendinblue.png"
                            title="Sendinblue"
                            alt="Sendinblue"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/mail-chimp.png"
                            title="Mail Chimp"
                            alt="Mail Chimp"
                        />
                    </li>
                </ul>

                <span>CRM</span>
                <ul>
                    <li>
                        <img
                            src="images/partenaire-ext/woocommerce.png"
                            title="Woocommerce"
                            alt="Woocommerce"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/wordpress.png"
                            title="Wordpress"
                            alt="Wordpress"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/prestashop.png"
                            title="Prestashop"
                            alt="Prestashop"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/sap.png"
                            title="SAP"
                            alt="SAP"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/sellsy.png"
                            title="SELLSY"
                            alt="SELLSY"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/hubspot.png"
                            title="Hubspot"
                            alt="Hubspot"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/slack.png"
                            title="Slack"
                            alt="Slack"
                        />
                    </li>
                </ul>

                <span v-text="$t('auth.partner.ads_systems')"></span>
                <ul>
                    <li>
                        <img
                            src="images/partenaire-ext/facebook-ads.png"
                            title="Facebook Ads"
                            alt="Facebook Ads"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/taboola.png"
                            title="Taboola"
                            alt="Taboola"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/prestashop.png"
                            title="Prestashop"
                            alt="Prestashop"
                        />
                    </li>
                </ul>

                <span v-text="$t('auth.partner.payment_systems')"></span>
                <ul>
                    <li>
                        <img
                            src="images/partenaire-ext/gocardless.png"
                            title="Gocardless"
                            alt="Gocardless"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/stripe.png"
                            title="Stripe"
                            alt="Stripe"
                        />
                    </li>
                </ul>

                <span v-text="$t('auth.partner.accounting_systems')"></span>
                <ul>
                    <li>
                        <img
                            src="images/partenaire-ext/sage.png"
                            title="SAGE"
                            alt="SAGE"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/ebp.png"
                            title="EBP"
                            alt="EBP"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/pennylane.png"
                            title="Pennylane"
                            alt="Pennylane"
                        />
                    </li>
                    <li>
                        <img
                            src="images/partenaire-ext/qonto.png"
                            title="QONTO"
                            alt="QONTO"
                        />
                    </li>
                </ul>
            </div>

            <div
                class="hc-login-bloc"
                style="font-size: 12px; text-align: center"
                v-text="$t('copyright')"
            ></div>
        </div>

        <flash />
    </div>
</template>

<style>
#hc-login-wrapper {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 50px 0;
    background-color: #f6f4f9;
    background-image: url(/images/bg.jpg);
    background-size: cover;
    background-position: center;
    overflow: auto;
}

#hc-login {
    max-width: 100%;
    width: auto;
    height: auto;
    display: flex;
    flex-direction: column;
    border-radius: 10px;
    gap: 10px;
}

.hc-login-bloc {
    width: 340px;
    padding: 30px;
    justify-content: center;
    align-items: center;
    display: flex;
    flex-direction: column;
    gap: 30px;
    background-color: #fffb;
}

#hc-login-form-logo {
    width: 160px;
}

#hc-login-form-title {
    font-size: 22px;
    font-weight: bold;
}

#hc-login-form-content {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

#hc-login-form-inputs {
    width: 100%;
    display: flex;
    flex-direction: column;
}

#hc-login-form-inputs > label {
    height: 40px;
    width: 100%;
    margin-bottom: -1px;
    position: relative;
}

#hc-login-form-inputs > label > input {
    border: 1px solid #ccc;
    width: 100%;
    height: 100%;
    padding: 0 10px;
    font-size: 13px;
}

#hc-login-form-input-login {
    border-radius: 5px 5px 0 0;
}

#hc-login-form-input-password {
    border-radius: 0 0 5px 5px;
}

#hc-login-form-submit {
    background: linear-gradient(121deg, #7939b8 0%, #7a59d8 100%);
    border: none;
    border-radius: 5px;
    color: white;
    height: 40px;
}

#hc-login-forgotten {
    text-align: right;
    color: #7939b8;
    font-size: 14px;
    text-decoration: underline;
}

#hc-login-form-other {
    display: flex;
    flex-direction: column;
    width: 100%;
    gap: 3px;
}

#hc-login-sign-in {
}

#hc-login-sign-in > a {
    font-size: 14px;
    text-decoration: none;
    background: linear-gradient(121deg, #39b889 0%, #59bdd8 100%);
    border: none;
    border-radius: 5px;
    color: white;
    height: 40px;
    width: 100%;
    display: inline-block;
    text-align: center;
    line-height: 40px;
}

#hc-login-google {
    border-radius: 5px;
    border: 2px solid #7939b8;
    background: linear-gradient(
        135deg,
        #f2eff5 0%,
        rgba(255, 255, 255, 0) 100%
    );
    height: 40px;
    color: #7939b8;
    text-align: center;
    line-height: 36px;
    font-weight: bold;
    font-size: 13px;
}

#hc-auth-infos-check {
    border-radius: 10px;
    margin: 0;
    background: white;
    padding: 0;
    list-style: none;
}

#hc-auth-infos-check > li {
    padding-left: 30px;
    position: relative;
    margin-bottom: 7px;
}

#hc-auth-infos-check > li::after {
    content: "";
    display: inline-block;
    position: absolute;
    top: 3px;
    left: 7px;
    width: 6px;
    height: 10px;
    border-right: 2px solid #7939b8;
    border-bottom: 2px solid #7939b8;
    transform: rotate(45deg);
}

#hc-auth-infos-check > li > #hc-auth-team-service {
    margin-top: 7px;
}

#auth-external-partner {
    gap: 0;
}

#auth-external-partner > span {
    font-weight: 600;
    display: inline-block;
    margin-top: 15px;
    margin-bottom: 5px;
    width: 100%;
    text-align: center;
}

#auth-external-partner > ul {
    font-size: 0;
    margin: 0;
    padding: 0;
}

#auth-external-partner > ul > li {
    width: 20%;
    display: inline-block;
    padding: 5px;
    text-align: center;
}

#auth-external-partner > ul > li > img {
    max-width: 100%;
}

#hc-login-email-autocompletion {
    position: absolute;
    width: 100%;
    top: 40px;
}
</style>

<script>
import { mapActions } from "vuex";
import hello from "hellojs";

import EmailAutocompletion from "./email-autocompletion/Autocompletion.vue";

export default {
    name: "login",

    components: {
        EmailAutocompletion,
    },

    data() {
        return {
            auth: {
                email: "",
                password: "",
            },
            processing: false,
            showPassword: false,
        };
    },
    methods: {
        ...mapActions({
            signIn: "auth/login",
        }),
        async login() {
            this.processing = true;
            await axios.get("/sanctum/csrf-cookie");
            await axios
                .post("/login", this.auth)
                .then(({ data }) => {
                    // Une session déjà authentifiée fait rediriger le
                    // middleware "guest" vers "/" (200, mais sans ce
                    // message) : ne pas considérer ça comme un login réussi.
                    if (data && data.message === "Login successful") {
                        this.signIn();
                    } else {
                        flashError({
                            title: "Authentification",
                            body: this.$t("auth.login.error"),
                        });
                    }
                })
                .catch((error) => {
                    if (
                        !(
                            error.response &&
                            error.response.status === 422 &&
                            error.response.data &&
                            error.response.data.message ==
                                "Two factors validation"
                        )
                    ) {
                        flashError({
                            title: "Authentification",
                            body: this.$t("auth.login.error"),
                        });
                    }
                })
                .finally(() => {
                    this.processing = false;
                });
        },
        async google() {
            hello("google")
                .login({ scope: "email" })
                .then(
                    async (e) => {
                        await axios.get("/sanctum/csrf-cookie");
                        await axios
                            .post("/api/google/auth/login", {
                                access_token: e.authResponse.access_token,
                            })
                            .then(({ data }) => {
                                this.signIn();
                            })
                            .catch((error) => {
                                // alert(data.message);
                            })
                            .finally(() => {
                                this.processing = false;
                            });
                    },
                    (error) => {}
                );
        },
    },

    computed: {},
};
</script>
