<?php

namespace App\Workflows\Actions;

/**
 * The design of an automatic email, chosen graphically in the builder: not
 * every project writes to its prospects the same way, so the look (header,
 * colours, signature, footer) belongs to each email.
 *
 * Only values the design knows are kept: colours are "#rrggbb", the font is
 * one of a few that mail clients all have, the texts are plain text. They are
 * written into the HTML of the email, which is why nothing else is accepted.
 */
class EmailDesign
{
    /**
     * branded: a coloured header and a card, like the CRM's own emails
     * simple: only the text, as a person would write it
     */
    public const STYLES = ['branded', 'simple'];

    public const FONTS = [
        'arial' => 'Arial, Helvetica, sans-serif',
        'verdana' => 'Verdana, Geneva, sans-serif',
        'tahoma' => 'Tahoma, Geneva, sans-serif',
        'trebuchet' => "'Trebuchet MS', Helvetica, sans-serif",
        'georgia' => "Georgia, 'Times New Roman', serif",
        'times' => "'Times New Roman', Times, serif",
    ];

    public const COLORS = ['header_color', 'accent_color', 'background_color', 'text_color'];

    public const TEXTS = [
        'brand_name' => 255,
        'brand_label' => 255,
        'category' => 255,
        'closing' => 255,
        'signature_name' => 255,
        'sent_by' => 500,
        'rights' => 500,
    ];

    public const FLAGS = ['show_title', 'show_signature', 'show_footer'];

    public static function defaults(?string $projectName = null): array
    {
        $name = (string) $projectName;

        return [
            'style' => 'branded',
            'font' => 'arial',
            'header_color' => '#163b52',
            'accent_color' => '#4a8796',
            'background_color' => '#f3f6f8',
            'text_color' => '#42515b',
            // "project": the logo of the project, in the header
            'logo' => 'none',
            'show_title' => true,
            'show_signature' => true,
            'show_footer' => true,
            'brand_name' => $name,
            'brand_label' => '',
            'category' => '',
            'closing' => 'Bien cordialement,',
            'signature_name' => $name,
            'sent_by' => $name !== '' ? "Un message envoyé par $name." : '',
            'rights' => $name !== '' ? '© ' . date('Y') . " $name · Tous droits réservés." : '',
        ];
    }

    /**
     * Problems of a design received (empty: it can be used)
     *
     * @return string[]
     */
    public static function problems(mixed $design, string $label): array
    {
        if ($design === null) {
            return [];
        }

        if (!is_array($design)) {
            return ["Le design de l'email de l'action \"$label\" n'est pas valide."];
        }

        $errors = [];

        if (isset($design['style']) && !in_array($design['style'], self::STYLES, true)) {
            $errors[] = "Le style de l'email de l'action \"$label\" n'existe pas.";
        }

        if (isset($design['font']) && !isset(self::FONTS[$design['font']])) {
            $errors[] = "La police de l'email de l'action \"$label\" n'existe pas.";
        }

        foreach (self::COLORS as $key) {
            if (isset($design[$key]) && !self::isColor($design[$key])) {
                $errors[] = "Une couleur de l'email de l'action \"$label\" n'est pas valide (format #rrggbb).";
                break;
            }
        }

        foreach (self::TEXTS as $key => $max) {
            if (isset($design[$key]) && (!is_string($design[$key]) || mb_strlen($design[$key]) > $max)) {
                $errors[] = "Un texte de l'email de l'action \"$label\" est trop long ($max caractères au plus).";
                break;
            }
        }

        if (isset($design['logo']) && !in_array($design['logo'], ['none', 'project'], true)) {
            $errors[] = "Le logo de l'email de l'action \"$label\" n'est pas valide.";
        }

        return $errors;
    }

    /**
     * The design, complete: what was chosen where it is valid, the default elsewhere
     */
    public static function normalize(mixed $design, ?string $projectName = null): array
    {
        $design = is_array($design) ? $design : [];
        $result = self::defaults($projectName);

        if (in_array($design['style'] ?? null, self::STYLES, true)) {
            $result['style'] = $design['style'];
        }

        if (isset(self::FONTS[$design['font'] ?? ''])) {
            $result['font'] = $design['font'];
        }

        foreach (self::COLORS as $key) {
            if (self::isColor($design[$key] ?? null)) {
                $result[$key] = strtolower($design[$key]);
            }
        }

        if (in_array($design['logo'] ?? null, ['none', 'project'], true)) {
            $result['logo'] = $design['logo'];
        }

        foreach (self::FLAGS as $key) {
            if (array_key_exists($key, $design)) {
                $result[$key] = filter_var($design[$key], FILTER_VALIDATE_BOOLEAN);
            }
        }

        // A text can be emptied on purpose (no label, no footer line)
        foreach (self::TEXTS as $key => $max) {
            if (array_key_exists($key, $design) && is_scalar($design[$key])) {
                $result[$key] = mb_substr(trim(strip_tags((string) $design[$key])), 0, $max);
            }
        }

        return $result;
    }

    public static function isColor(mixed $value): bool
    {
        return is_string($value) && preg_match('/^#[0-9a-f]{6}$/i', $value) === 1;
    }

    public static function fontStack(string $font): string
    {
        return self::FONTS[$font] ?? self::FONTS['arial'];
    }

    /**
     * The whole email: the design around the body (HTML, already cleaned)
     */
    public static function render(array $design, string $subject, string $body, string $projectName, ?string $logo = null): string
    {
        return view('emails.workflow-message', [
            'design' => self::normalize($design, $projectName),
            'subject' => $subject,
            'body' => $body,
            'projectName' => $projectName,
            'logo' => $logo,
            'fontStack' => self::fontStack(self::normalize($design, $projectName)['font']),
        ])->render();
    }
}
