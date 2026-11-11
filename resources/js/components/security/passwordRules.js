// Mêmes règles que App\Rules\StrongPassword (back) : le serveur reste juge, ceci n'est que l'aide à la saisie.
export const MIN_LENGTH = 12;

export function passwordChecks(password = "") {
    return [
        {
            key: "length",
            label: `Au moins ${MIN_LENGTH} caractères`,
            ok: password.length >= MIN_LENGTH,
        },
        {
            key: "lower",
            label: "Une lettre minuscule",
            ok: /\p{Ll}/u.test(password),
        },
        {
            key: "upper",
            label: "Une lettre majuscule",
            ok: /\p{Lu}/u.test(password),
        },
        { key: "digit", label: "Un chiffre", ok: /\d/.test(password) },
        {
            key: "symbol",
            label: "Un symbole (! @ # $ % * ? - _)",
            ok: /[^\p{L}\d]/u.test(password),
        },
    ];
}

export function isStrongPassword(password = "") {
    return passwordChecks(password).every((check) => check.ok);
}
