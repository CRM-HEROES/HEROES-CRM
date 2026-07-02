<?php

namespace App\Observers;

use App\Models\Field;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FieldObserver
{
    /**
     * Handle the Field "creating" event.
     *
     * @param  \App\Models\Field  $field
     * @return void
     */
    public function creating(Field $field)
    {
        // Slug
        if ($field->meta) {
            $field->slug = $this->getAvailableSlug($field);
        }
        
        // $field->order = $this->getOrder($field);
    }

    /**
     * Handle the Field "updating" event.
     */
    public function updating(Field $field): void
    {
        // $this->updateOrder($field);
    }

    /**
     * Handle the Field "deleted" event.
     */
    public function deleted(Field $field): void
    {
        // $this->deleteOrder($field);
    }

    /**
     * Get available slug
     *
     * @param  \App\Models\Field  $field
     * @return void
     */
    protected function getAvailableSlug(Field $field)
    {
        $defaultSlug = Str::slug($field->name, '_');
        $defaultSlug = substr($defaultSlug, 0, 200);

        for ($i = 1;; ++$i) {
            $slug = $i == 1 ? 
                $defaultSlug : 
                $defaultSlug . "_" . $i;

            // Skip if slug already belongs to another field
            if (Field::where('project_id', $field->project_id)->where('slug', $slug)->first()) {
                continue;
            }

            // Available slug
            return $slug;
        }
    }

    /**
     * Get order
     *
     * @param  \App\Models\Field  $field
     * @return void
     */
    protected function getOrder(Field $field)
    {
        return Field
            ::where('project_id', $field->project_id)
            ->where('for', $field->for)
            ->where('meta', $field->meta)
            ->count() + 1;
    }

    /**
     * Update order
     *
     * @param  \App\Models\Field  $field
     * @return void
     */
    protected function updateOrder(Field $field)
    {
        if (!$field->isDirty('order')) {
            return;
        }

        $count = DB::table('fields')
            ->where('project_id', $field->project_id)
            ->where('for', $field->for)
            ->where('meta', $field->meta)
            ->count();

        $field->order = max(1, min($field->order, $count));

        $oldOrder = $field->getOriginal('order');
        $newOrder = $field->order;

        if ($oldOrder < $newOrder) {
            DB::table('fields')
                ->where('project_id', $field->project_id)
                ->where('for', $field->for)
                ->where('meta', $field->meta)
                ->where('order', '>', 0)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->update([
                    'order' => DB::raw('`order` - 1')
                ]);
        } else if ($oldOrder > $newOrder) {
            DB::table('fields')
                ->where('project_id', $field->project_id)
                ->where('for', $field->for)
                ->where('meta', $field->meta)
                ->where('order', '>', 0)
                ->where('order', '>=', $newOrder)
                ->where('order', '<', $oldOrder)
                ->update([
                    'order' => DB::raw('`order` + 1')
                ]);
        }
    }

    /**
     * Delete order
     *
     * @param  \App\Models\Field  $field
     * @return void
     */
    protected function deleteOrder(Field $field)
    {
        DB::table('fields')
            ->where('project_id', $field->project_id)
            ->where('for', $field->for)
            ->where('meta', $field->meta)
            ->where('order', '>=', $field->order)
            ->update([
                'order' => DB::raw('`order` - 1')
            ]);
    }
}
