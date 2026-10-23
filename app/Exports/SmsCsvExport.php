<?php

namespace App\Exports;

use App\Models\Project;
use Carbon\CarbonImmutable;
use Generator;
use Illuminate\Support\Facades\DB;

class SmsCsvExport
{
    public const HEADERS = [
        'sms_id', 'prospect_id', 'prospect_name', 'prospect_phone_number', 'prospect_mobile_phone_number',
        'direction', 'source', 'message', 'created_at_utc', 'sent_at_utc', 'status', 'error',
        'creator_id', 'creator_name', 'creator_email', 'from_user', 'is_incoming',
        'prospect_deleted_at_utc', 'media_json', 'raw_json',
    ];

    public function rows(Project $project, CarbonImmutable $from, CarbonImmutable $until, int $count = 0): Generator
    {
        if ($count < 0) {
            throw new \InvalidArgumentException('Le nombre de SMS doit être positif ou nul.');
        }
        yield self::HEADERS;

        // SMS have no project_id: include only those belonging to this project's prospects.
        // Deleted prospects still present in the database retain their historical messages.
        $query = DB::table('sms')
            ->join('prospects', 'prospects.id', '=', 'sms.prospect_id')
            ->leftJoin('users', 'users.id', '=', 'sms.creator_id')
            ->where('prospects.project_id', $project->id)
            ->where('sms.created_at', '>=', $from->format('Y-m-d H:i:s'))
            ->where('sms.created_at', '<', $until->format('Y-m-d H:i:s'));

        // New inserts cannot keep extending a download that is already in progress.
        $lastId = (clone $query)->max('sms.id');
        if ($lastId === null) {
            return;
        }

        $query->where('sms.id', '<=', $lastId)->select([
            'sms.id as sms_id', 'sms.prospect_id', 'sms.message', 'sms.source', 'sms.error',
            'sms.from_user', 'sms.is_incoming', 'sms.created_at', 'sms.sent_at', 'sms.creator_id', 'sms.media',
            'prospects.first_name', 'prospects.last_name', 'prospects.company_name',
            'prospects.phone_number', 'prospects.mobile_phone_number', 'prospects.deleted_at as prospect_deleted_at',
            'users.name as creator_name', 'users.email as creator_email',
        ]);

        $exported = 0;
        foreach ($query->lazyById(500, 'sms.id', 'sms_id') as $sms) {
            $incoming = (bool) $sms->is_incoming || !(bool) $sms->from_user;
            $name = trim(($sms->first_name ?? '') . ' ' . ($sms->last_name ?? ''));
            $status = $incoming ? 'received'
                : (trim((string) $sms->error) !== '' ? 'failed' : ($sms->sent_at !== null ? 'sent' : 'pending'));
            $raw = json_encode($sms, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);

            yield [
                $sms->sms_id, $sms->prospect_id, $name !== '' ? $name : $sms->company_name,
                $sms->phone_number, $sms->mobile_phone_number, $incoming ? 'incoming' : 'outgoing',
                $sms->source, $sms->message, $sms->created_at, $sms->sent_at, $status, $sms->error,
                $sms->creator_id, $sms->creator_name, $sms->creator_email,
                (int) (bool) $sms->from_user, (int) (bool) $sms->is_incoming,
                $sms->prospect_deleted_at, $sms->media, $raw,
            ];
            if ($count > 0 && ++$exported >= $count) {
                return;
            }
        }
    }

    public function write($output, Project $project, CarbonImmutable $from, CarbonImmutable $until, int $count = 0): int
    {
        if (fwrite($output, "\xEF\xBB\xBF") !== 3) {
            throw new \RuntimeException('Impossible d’écrire le fichier CSV des SMS.');
        }
        $rows = -1; // The header is not a message.
        foreach ($this->rows($project, $from, $until, $count) as $row) {
            $safe = array_map(function ($value): string {
                $text = (string) $value;
                // Quotes alone do not prevent spreadsheet formulas; raw_json retains the original values.
                return preg_match('/^[\s\x00-\x20]*[=+@-]/u', $text) ? "'" . $text : $text;
            }, $row);
            if (fputcsv($output, $safe, ',', '"', '', "\r\n") === false) {
                throw new \RuntimeException('Impossible d’écrire le fichier CSV des SMS.');
            }
            ++$rows;
        }
        return $rows;
    }
}
