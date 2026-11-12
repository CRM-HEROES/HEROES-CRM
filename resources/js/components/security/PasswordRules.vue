<template>
    <ul class="password-rules" aria-live="polite">
        <li
            v-for="check in checks"
            :key="check.key"
            :class="check.ok ? 'ok' : 'ko'"
        >
            <span class="mark" aria-hidden="true">{{ check.ok ? "✓" : "✗" }}</span>
            {{ check.label }}
        </li>
    </ul>
</template>

<script>
import { passwordChecks } from "./passwordRules";

export default {
    name: "PasswordRules",
    props: {
        password: { type: String, default: "" },
    },
    computed: {
        checks() {
            return passwordChecks(this.password);
        },
    },
};
</script>

<style scoped>
.password-rules {
    list-style: none;
    margin: 0 0 1rem;
    padding: 0;
    font-size: 0.85rem;
}

.password-rules li {
    display: flex;
    gap: 0.4rem;
    margin-bottom: 0.15rem;
}

.password-rules .ok {
    color: #1a7f37;
}

.password-rules .ko {
    color: #6b6b6b;
}

.mark {
    width: 1rem;
    font-weight: 700;
}
</style>
