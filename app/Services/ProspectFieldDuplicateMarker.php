<?php

namespace App\Services;

use App\Models\Field;
use App\Models\Prospect;

class ProspectFieldDuplicateMarker
{
    public function mark(Prospect $prospect): void
    {
        $fields = Field::query()
            ->where('project_id', $prospect->project_id)
            ->where('for', 'prospect')
            ->where('meta', false)
            ->where('unique', true)
            ->whereIn('slug', ['email', 'mobile_phone_number', 'phone_number'])
            ->get(['slug']);

        $ownFields = collect($prospect->duplicate_fields ?: []);
        $groupIds = collect([$prospect->duplicate_group_id])->filter();

        foreach ($fields as $field) {
            $value = $prospect->{$field->slug};
            if (blank($value)) {
                continue;
            }

            $matches = Prospect::query()
                ->where('project_id', $prospect->project_id)
                ->where('id', '!=', $prospect->id)
                ->where($field->slug, $value)
                ->get(['id', 'duplicate_group_id', 'duplicate_fields']);

            if ($matches->isEmpty()) {
                continue;
            }

            $ids = $matches->pluck('id')->push($prospect->id);
            $groupId = $matches->pluck('duplicate_group_id')->filter()->min() ?? $ids->min();
            $groupIds->push($groupId);
            $ownFields->push($field->slug);

            foreach ($matches as $match) {
                $matchFields = collect($match->duplicate_fields ?: [])->push($field->slug)->unique()->values();
                $matchGroup = $match->duplicate_group_id ?: $groupId;
                Prospect::query()->whereKey($match->id)->update([
                    'duplicate_group_id' => $matchGroup,
                    'duplicate_fields' => $matchFields->isEmpty() ? null : $matchFields->toJson(),
                ]);
            }
        }

        $ownFields = $ownFields->unique()->values();
        if ($ownFields->isNotEmpty()) {
            $prospect->duplicate_group_id = $groupIds->min() ?: $prospect->id;
            $prospect->duplicate_fields = $ownFields->all();
            $prospect->saveQuietly();
        }
    }
}
