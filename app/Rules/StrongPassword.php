<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Règle unique de mot de passe fort, utilisée partout où un utilisateur choisit un mot de passe :
 * au moins 12 caractères, une minuscule, une majuscule, un chiffre et un symbole.
 * (La même règle est affichée en direct dans le front : resources/js/components/security/PasswordRules.vue)
 */
class StrongPassword implements ValidationRule
{
    public const MIN_LENGTH = 12;

    /**
     * @return string[] les exigences non respectées (vide si le mot de passe est valide)
     */
    public static function missing(string $password): array
    {
        $missing = [];

        if (mb_strlen($password) < self::MIN_LENGTH) {
            $missing[] = 'au moins ' . self::MIN_LENGTH . ' caractères';
        }
        if (!preg_match('/\p{Ll}/u', $password)) {
            $missing[] = 'une lettre minuscule';
        }
        if (!preg_match('/\p{Lu}/u', $password)) {
            $missing[] = 'une lettre majuscule';
        }
        if (!preg_match('/\d/', $password)) {
            $missing[] = 'un chiffre';
        }
        if (!preg_match('/[^\p{L}\d]/u', $password)) {
            $missing[] = 'un symbole (par exemple ! @ # $ % * ? - _)';
        }

        return $missing;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $missing = self::missing((string) $value);

        if ($missing) {
            $fail('Le mot de passe doit contenir : ' . implode(', ', $missing) . '.');
        }
    }
}
