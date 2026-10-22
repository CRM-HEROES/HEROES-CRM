<?php

namespace App\Exports;

use App\Models\Project;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;

class ProspectHistoryFormatter
{
    private Project $project;
    private array $relationNames = [];

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function format(array $history): string
    {
        $entries = [];

        foreach ($history as $item) {
            $type = $item['type'] ?? '';
            $author = in_array($type, ['revision', 'event'], true)
                ? ($item['user'] ?? null)
                : ($item['creator'] ?? null);
            $date = $type === 'event'
                ? ($item['started_at'] ?? $item['created_at'] ?? null)
                : ($item['created_at'] ?? null);
            $heading = $this->join([
                $this->date($date),
                trans('prospect.export.history_types.' . $type),
                data_get($author, 'name'),
            ], ' - ');
            $entries[] = $this->join([$heading, $this->content($item)], "\n");
        }

        return implode("\n\n", $entries);
    }

    private function content(array $item): string
    {
        switch ($item['type'] ?? '') {
            case 'revision':
                $change = $this->text($item['old_value'] ?? null)
                    . ' → ' . $this->text($item['new_value'] ?? null);

                return $this->join([$item['field'] ?? $item['key'] ?? null, $change], ' : ');

            case 'event':
                return $this->join([
                    data_get($item, 'calendar.name'),
                    $item['location'] ?? null,
                    $this->plainText($item['description'] ?? null),
                ], "\n");

            case 'file':
                return $this->join([data_get($item, 'folder.name'), $item['name'] ?? null], ' : ');

            case 'group':
            case 'user':
            case 'order':
                return $this->text($item['name'] ?? null);

            case 'interaction':
                return $this->source($item['source'] ?? null);

            case 'label':
                return $this->join([
                    $this->relationName('categories', $item['category_id'] ?? null),
                    $item['name'] ?? null,
                ], ' : ');

            case 'link':
                return $this->join([$item['name'] ?? null, $item['link'] ?? null], ' : ');

            case 'message':
                return $this->join([
                    $this->relationName('threads', $item['thread_id'] ?? null),
                    $this->plainText($item['body'] ?? null),
                ], "\n");

            case 'sms':
                return $this->join([
                    $this->source($item['source'] ?? null),
                    $item['message'] ?? null,
                ], "\n");
        }

        return '';
    }

    private function relationName(string $relation, $id): string
    {
        if ($id === null) {
            return '';
        }

        if (!array_key_exists($relation, $this->relationNames)) {
            $this->relationNames[$relation] = $this->project->{$relation}->pluck('name', 'id')->all();
        }

        return $this->text($this->relationNames[$relation][$id] ?? null);
    }

    private function source($source): string
    {
        $source = $this->text($source);

        return [
            'ringover' => 'Ringover',
            'aircall' => 'Aircall',
            'cloudtalk' => 'CloudTalk',
            'whatsapp' => 'WhatsApp',
            'smsbox' => 'SMSBOX',
            'ultramsg' => 'UltraMsg',
            'mtarget' => 'MTarget',
        ][$source] ?? $source;
    }

    private function date($date): string
    {
        if ($date === null || $date === '') {
            return '';
        }

        try {
            return Carbon::parse($date)->format('d/m/Y H:i');
        } catch (\Throwable $exception) {
            return $this->text($date);
        }
    }

    private function join(array $parts, string $separator): string
    {
        return implode($separator, array_values(array_filter(
            array_map(fn ($part) => $this->text($part), $parts),
            static fn (string $part) => $part !== ''
        )));
    }

    private function text($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('d/m/Y H:i');
        }

        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        } elseif ($value instanceof \JsonSerializable) {
            $value = $value->jsonSerialize();
        } elseif ($value instanceof \Stringable) {
            $value = (string) $value;
        } elseif (is_object($value)) {
            $value = get_object_vars($value);
        }

        if (is_array($value)) {
            $parts = [];
            $isList = array_is_list($value);
            foreach ($value as $key => $part) {
                $parts[] = ($isList ? '' : $key . ': ') . $this->text($part);
            }

            return implode('; ', $parts);
        }

        if (!is_scalar($value)) {
            return '';
        }

        return trim(str_replace(["\r\n", "\r"], "\n", (string) $value));
    }

    private function plainText($value): string
    {
        $text = preg_replace('/<br\s*\/?\s*>|<\/(?:p|div|li|h[1-6])\s*>/i', "\n", $this->text($value));
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(str_replace(["\r\n", "\r", "\xc2\xa0"], ["\n", "\n", ' '], $text));
    }
}
