import { reactive, watch } from "vue";

const KEY = "heroes.prospects.ruler";

function load() {
    try {
        return JSON.parse(localStorage.getItem(KEY)) || {};
    } catch (e) {
        return {};
    }
}

const saved = load();

// État partagé : réglage utilisateur (bouton) + position de la règle.
export const screenRuler = reactive({
    enabled: !!saved.enabled,
    vertical: !!saved.vertical,
    x: Number.isFinite(saved.x) ? saved.x : 80,
    y: Number.isFinite(saved.y) ? saved.y : 160,
    length: Number.isFinite(saved.length) ? saved.length : 600,
    color: /^#[0-9a-f]{6}$/i.test(saved.color) ? saved.color : "#ffe78c",
});

watch(
    () => ({ ...screenRuler }),
    (value) => {
        try {
            localStorage.setItem(KEY, JSON.stringify(value));
        } catch (e) {
            //
        }
    },
);
