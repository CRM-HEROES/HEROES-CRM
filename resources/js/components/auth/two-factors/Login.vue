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
                    <div id="hc-two-factors-form-inputs">
                        <label>
                            <span
                                v-text="
                                    $t('auth.google_authenticator.key_security')
                                "
                            ></span>
                            <input
                                ref="totp"
                                v-model="totp"
                                name="totp"
                                id="hc-two-factors-form-input-password"
                                :placeholder="
                                    $t('auth.google_authenticator.key_security')
                                "
                            />
                        </label>
                    </div>
                    <button
                        id="hc-two-factors-form-submit"
                        type="submit"
                        :disabled="validatingTwoFactors"
                        @click="validateTwoFactors"
                    >
                        {{
                            $t("auth.google_authenticator.login") +
                            (validatingTwoFactors ? " ..." : "")
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style>
#hc-two-factors-wrapper {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background-color: #f6f4f9;
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
    padding: 30px;
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
import { mapActions } from "vuex";

export default {
    name: "login",
    data() {
        return {
            qrcode: null,
            totp: "",
            fetchingQrcode: false,
            validatingTwoFactors: false,
        };
    },

    mounted() {
        this.$refs.totp.focus();
    },

    methods: {
        ...mapActions({
            signIn: "auth/login",
        }),

        async validateTwoFactors() {
            this.validatingTwoFactors = true;
            try {
                const { data } = await axios.post(
                    "/api/google/authenticator/login",
                    {
                        totp: this.totp,
                    }
                );
                this.validatingTwoFactors = false;
                this.signIn();
            } catch (e) {
            } finally {
                this.validatingTwoFactors = false;
            }
        },
    },
};
</script>
