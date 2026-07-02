<template>
    <div id="hc-two-factors-wrapper">
        <div id="hc-two-factors">
            <form
                id="hc-two-factors-form"
                action="javascript:void(0)"
                method="post"
            >
                <img id="hc-two-factors-form-logo" src="/images/logo.png" />
                <h1 id="hc-two-factors-form-title">Google Authenticator</h1>
                <div id="hc-two-factors-form-content">
                    <item
                        tag="label"
                        style="
                            padding: 10px;
                            gap: 0px;
                            flex-direction: row;
                            background-color: #fdf4e6;
                            border-radius: 10px;
                            margin-bottom: 10px;
                            color: #8b4a15;
                            align-items: start;
                        "
                    >
                        <icon
                            class="fa fa-lightbulb"
                            style="
                                position: absolute;
                                right: -17px;
                                top: -17px;
                                width: 34px;
                                height: 34px;
                                background-color: rgb(255, 223, 173);
                                color: rgb(225, 126, 0);
                                border-radius: 50%;
                                transform: rotate(24deg);
                            "
                        />
                        <div
                            class="hc-item-main-content"
                            style="white-space: normal"
                        >
                            {{
                                "Activer Google Authenticator sur votre compte ajoute une sécurité supplémentaire en exigeant un code de vérification unique après votre mot de passe."
                            }}
                            <br />
                            {{
                                "Cela rend l'accès à votre compte beaucoup plus difficile pour les personnes non autorisées, renforçant ainsi la protection de vos données et de votre confidentialité."
                            }}
                        </div>
                    </item>

                    <div id="hc-two-factors-form-inputs">
                        <label id="hc-two-factors-qrcode">
                            <span
                                style="
                                    text-align: center;
                                    margin-bottom: 10px;
                                    font-weight: bold;
                                "
                                v-html="$t('auth.google_authenticator.open')"
                            ></span>
                            <span
                                style="text-align: center"
                                v-text="
                                    $t('auth.google_authenticator.scan_qrcode')
                                "
                            >
                            </span>
                            <div
                                style="
                                    position: relative;
                                    width: 100%;
                                    height: 300px;
                                "
                            >
                                <img
                                    v-if="qrcode"
                                    style="width: 100%; height: 100%"
                                    alt="Image of QR barcode"
                                    :src="qrcode"
                                />
                                <loading :loading="fetchingQrcode" />
                            </div>
                        </label>
                        <label id="hc-two-factors-secret">
                            <span
                                style="text-align: center"
                                v-text="
                                    $t(
                                        'auth.google_authenticator.copy_following_key_configuration'
                                    )
                                "
                            ></span>
                            <a
                                style="
                                    position: relative;
                                    width: 100%;
                                    text-align: center;
                                    margin-top: 10px;
                                    margin-bottom: 20px;
                                    font-size: 18px;
                                    letter-spacing: 2px;
                                    cursor: pointer;
                                "
                                :href="
                                    'otpauth://totp/dev@it.com?secret=' +
                                    secret +
                                    '&issuer=HeroesCRM'
                                "
                                @click.prevent="copyConfigurationKey"
                            >
                                <span v-text="secret"></span>
                                <i
                                    class="fa fa-copy"
                                    style="margin-left: 12px; color: #999"
                                ></i>
                            </a>
                        </label>
                        <label>
                            <span
                                v-text="
                                    $t('auth.google_authenticator.key_security')
                                "
                            ></span>
                            <input
                                v-model="totp"
                                name="totp"
                                id="hc-two-factors-form-input-password"
                                :placeholder="
                                    $t('auth.google_authenticator.key_security')
                                "
                            />
                        </label>
                        <item
                            tag="label"
                            style="
                                padding: 10px;
                                gap: 0px;
                                flex-direction: row;
                                background-color: #ede6fd;
                                border-radius: 10px;
                                margin-top: 10px;
                                color: #793dbc;
                            "
                        >
                            <icon class="fa fa-question icon-blue" />
                            <div
                                class="hc-item-main-content"
                                style="white-space: normal"
                                v-text="
                                    $t('auth.google_authenticator.check_type')
                                "
                            ></div>
                            <checkbox v-model="checkTypeIpChanged" />
                        </item>
                    </div>
                    <button
                        id="hc-two-factors-form-submit"
                        type="submit"
                        :disabled="activatingTwoFactors"
                        @click="activateTwoFactors"
                    >
                        {{
                            activatingTwoFactors
                                ? $t("auth.google_authenticator.activating")
                                : $t("auth.google_authenticator.activate")
                        }}
                    </button>
                </div>
            </form>
        </div>

        <flash />
    </div>
</template>

<style>
#hc-two-factors-wrapper {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-top: 30px;
    padding-bottom: 30px;
    background-color: #f6f4f9;
    overflow: auto;
    background-image: url(/images/bg.jpg);
    background-size: cover;
    background-position: center;
}

#hc-two-factors {
    max-width: 100%;
    width: auto;
    height: auto;
    display: flex;
    flex-direction: row;
    border-radius: 10px;
}

#hc-two-factors-form {
    width: 340px;
    padding: 12px;
    justify-content: center;
    align-items: center;
    display: flex;
    flex-direction: column;
    gap: 30px;
    background-color: #fff8;
}

#hc-two-factors-form-logo {
    width: 160px;
}

#hc-two-factors-form-title {
    font-size: 22px;
    font-weight: bold;
}

#hc-two-factors-form-content {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

#hc-two-factors-form-inputs {
    width: 100%;
    display: flex;
    flex-direction: column;
}

#hc-two-factors-form-inputs > label {
    width: 100%;
    margin-bottom: -1px;
    display: flex;
    flex-direction: column;
}

#hc-two-factors-form-inputs > label > input {
    height: 40px;
    border: 1px solid #ccc;
    width: 100%;
    padding: 0 10px;
    font-size: 13px;
    border-radius: 5px;
}

#hc-two-factors-form-submit {
    background: linear-gradient(121deg, #7939b8 0%, #7a59d8 100%);
    border: none;
    border-radius: 5px;
    color: white;
    height: 40px;
}

#hc-two-factors-forgotten {
    text-align: right;
    color: #7939b8;
    font-size: 14px;
    text-decoration: underline;
}

#hc-two-factors-form-other {
    display: flex;
    flex-direction: column;
    width: 100%;
    gap: 3px;
}

#hc-two-factors-sign-in {
}

#hc-two-factors-sign-in > a {
    color: #7939b8;
    font-size: 14px;
    text-decoration: underline;
}

#hc-two-factors-google {
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
</style>

<script>
export default {
    name: "login",
    data() {
        return {
            qrcode: null,
            secret: "",
            totp: "",
            checkType: null,
            fetchingQrcode: false,
            activatingTwoFactors: false,
        };
    },

    created() {
        this.getQrCode();
    },

    methods: {
        async getQrCode() {
            this.fetchingQrcode = true;

            try {
                const { data } = await axios.get(
                    "/api/google/authenticator/enable"
                );
                this.fetchingQrcode = false;

                this.qrcode = data.qrcode;
                this.secret = data.secret;
            } catch (e) {
            } finally {
                this.fetchingQrcode = false;
            }
        },
        async activateTwoFactors() {
            this.activatingTwoFactors = true;

            if (this.totp.length != 6) {
                return;
            }

            try {
                const { data } = await axios.post(
                    "/api/google/authenticator/activate",
                    {
                        totp: this.totp,
                        check_type: this.checkType,
                    }
                );
                this.$router.push({
                    name: "dashboard",
                });
            } catch (e) {
            } finally {
                this.activatingTwoFactors = false;
            }
        },
        copyConfigurationKey() {
            navigator.clipboard.writeText(this.secret);

            flashInfo({
                title: "Google Authenticator",
                body: this.$t("auth.google_authenticator.key_security_copied"),
                duration: 5000,
            });
        },
    },

    computed: {
        checkTypeIpChanged: {
            get() {
                this.checkType == "ip_changed";
            },
            set(value) {
                this.checkType = value ? "ip_changed" : null;
            },
        },
    },
};
</script>
