<?php

namespace App\Agents\Tools;

/**
 * Checks the input of a tool call against its JSON Schema before the tool
 * runs. The API guarantees strict schemas, but the engine does not rely on it:
 * what comes from a model is validated on the server.
 *
 * Supported: type (string, integer, number, boolean, array, object), required,
 * additionalProperties: false, enum, minimum, maximum, minLength, maxLength,
 * items, maxItems. Enough for the tools of the CRM; anything else is ignored.
 */
class InputValidator
{
    /**
     * @return string[] the problems found (empty: valid)
     */
    public static function validate(array $schema, mixed $value, string $path = 'input'): array
    {
        $errors = [];
        $type = $schema['type'] ?? null;

        if ($type !== null && !self::isType($value, $type)) {
            return ["$path doit être de type $type."];
        }

        if (isset($schema['enum']) && !in_array($value, $schema['enum'], true)) {
            $errors[] = "$path doit valoir l'une de ces valeurs : " . implode(', ', $schema['enum']) . '.';
        }

        if (is_string($value)) {
            if (isset($schema['minLength']) && mb_strlen($value) < $schema['minLength']) {
                $errors[] = "$path est trop court.";
            }
            if (isset($schema['maxLength']) && mb_strlen($value) > $schema['maxLength']) {
                $errors[] = "$path est trop long.";
            }
        }

        if ((is_int($value) || is_float($value))) {
            if (isset($schema['minimum']) && $value < $schema['minimum']) {
                $errors[] = "$path doit être au moins {$schema['minimum']}.";
            }
            if (isset($schema['maximum']) && $value > $schema['maximum']) {
                $errors[] = "$path doit être au plus {$schema['maximum']}.";
            }
        }

        if ($type === 'array') {
            if (isset($schema['maxItems']) && count($value) > $schema['maxItems']) {
                $errors[] = "$path a trop d'éléments (maximum {$schema['maxItems']}).";
            }
            if (isset($schema['items'])) {
                foreach ($value as $index => $item) {
                    $errors = array_merge($errors, self::validate($schema['items'], $item, "{$path}[{$index}]"));
                }
            }
        }

        if ($type === 'object') {
            $properties = $schema['properties'] ?? [];

            foreach ($schema['required'] ?? [] as $required) {
                if (!array_key_exists($required, $value)) {
                    $errors[] = "$path.$required est obligatoire.";
                }
            }

            foreach ($value as $name => $item) {
                if (!isset($properties[$name])) {
                    if (($schema['additionalProperties'] ?? true) === false) {
                        $errors[] = "$path.$name n'est pas un paramètre connu.";
                    }
                    continue;
                }

                $errors = array_merge($errors, self::validate($properties[$name], $item, "$path.$name"));
            }
        }

        return $errors;
    }

    protected static function isType(mixed $value, string $type): bool
    {
        return match ($type) {
            'string' => is_string($value),
            'integer' => is_int($value),
            'number' => is_int($value) || is_float($value),
            'boolean' => is_bool($value),
            // An empty JSON object and an empty list are both [] once decoded
            'array' => is_array($value) && ($value === [] || array_is_list($value)),
            'object' => is_array($value) && ($value === [] || !array_is_list($value)),
            default => true,
        };
    }
}
