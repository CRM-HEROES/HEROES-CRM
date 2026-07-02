<template>
    <div id="hc-register-wrapper">
        <div id="hc-register">
            <form
                id="hc-register-form"
                action="javascript:void(0)"
                method="post"
            >
                <img id="hc-register-form-logo" src="/images/logo.png" />
                <h1
                    id="hc-register-form-title"
                    v-text="$t('auth.register.title')"
                ></h1>
                <div id="hc-register-form-content">
                    <div id="hc-register-form-inputs">
                        <label>
                            <input
                                type="text"
                                name="name"
                                v-model="user.name"
                                id="name"
                                :placeholder="$t('name')"
                                class="form-control"
                            />
                        </label>
                        <label>
                            <input
                                type="email"
                                name="email"
                                v-model="user.email"
                                id="email"
                                :placeholder="$t('auth.email')"
                                class="form-control"
                            />
                            <email-autocompletion
                                style="top: 36px"
                                v-model="user.email"
                            />
                        </label>
                        <label>
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                v-model="user.password"
                                id="password"
                                :placeholder="$t('auth.password')"
                                class="form-control"
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
                        <label>
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                name="password_confirmation"
                                v-model="user.password_confirmation"
                                id="password_confirmation"
                                :placeholder="$t('auth.confirm_password')"
                                class="form-control"
                            />
                        </label>
                    </div>
                    <button
                        id="hc-register-form-submit"
                        type="submit"
                        :disabled="processing"
                        @click="register"
                    >
                        {{
                            $t("auth.register.subscribe") +
                            (processing ? " ..." : "")
                        }}
                    </button>
                </div>
                <div id="hc-register-form-other">
                    <div id="hc-register-sign-in"></div>
                    <router-link
                        :to="{ name: 'login' }"
                        id="hc-register-login"
                        v-text="$t('auth.register.already_have_an_account')"
                    ></router-link>
                </div>
            </form>
        </div>
    </div>
</template>

<style>
#hc-register-wrapper {
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

#hc-register {
    max-width: 100%;
    width: auto;
    height: auto;
    display: flex;
    flex-direction: row;
    border-radius: 10px;
    overflow: hidden;
}

#hc-register-form {
    width: 340px;
    padding: 30px;
    justify-content: center;
    align-items: center;
    display: flex;
    flex-direction: column;
    gap: 30px;
    background-color: #fff8;
}

#hc-register-form-logo {
    width: 160px;
}

#hc-register-form-title {
    font-size: 22px;
    font-weight: bold;
}

#hc-register-form-content {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

#hc-register-form-inputs {
    width: 100%;
    display: flex;
    flex-direction: column;
}

#hc-register-form-inputs > label {
    height: 40px;
    width: 100%;
    margin-bottom: -1px;
    position: relative;
}

#hc-register-form-inputs > label > input {
    border: 1px solid #ccc;
    width: 100%;
    height: 100%;
    padding: 0 10px;
    font-size: 13px;
}

#hc-register-form-input-register {
    border-radius: 5px 5px 0 0;
}

#hc-register-form-input-password {
    border-radius: 0 0 5px 5px;
}

#hc-register-form-submit {
    background: linear-gradient(121deg, #7939b8 0%, #7a59d8 100%);
    border: none;
    border-radius: 5px;
    color: white;
    height: 40px;
}

#hc-register-forgotten {
    text-align: right;
    color: #7939b8;
    font-size: 14px;
    text-decoration: underline;
}

#hc-register-form-other {
    display: flex;
    flex-direction: column;
    width: 100%;
    gap: 3px;
}

#hc-register-sign-in {
}

#hc-register-sign-in > a {
    color: #7939b8;
    font-size: 14px;
    text-decoration: underline;
}

#hc-register-login {
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

import EmailAutocompletion from "./email-autocompletion/Autocompletion.vue";

export default {
    name: "register",

    components: {
        EmailAutocompletion,
    },

    data() {
        return {
            showPassword: false,
            user: {
                name: "",
                email: "",
                password: "",
                password_confirmation: "",
            },
            processing: false,
        };
    },
    methods: {
        ...mapActions({
            signIn: "auth/login",
        }),
        async register() {
            this.processing = true;
            await axios
                .post("/register", this.user)
                .then((response) => {
                    this.signIn();
                })
                .catch(({ response: { data } }) => {
                    alert(data.message);
                })
                .finally(() => {
                    this.processing = false;
                });
        },
    },
};
</script>
