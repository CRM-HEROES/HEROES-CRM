<?php

namespace App\Http\Controllers\API\Project\Import;

use App\Models\Category;
use App\Models\Import;
use App\Models\Project;
use App\Http\Controllers\Controller;
use App\Models\Thread;

class MappingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Import $import)
    {
        abort_unless($project->id == $import->project_id, 404);

        return $import->mapping;
    }

    /**
     * Update the specified resource in storage.
     */
    public function field(Project $project, Import $import, $index, $slug)
    {
        abort_unless($project->fields()->where('meta', false)->where('slug', $slug)->first(), 400);

        return $this->update($project, $import, $index, $slug);
    }

    /**
     * Update the specified resource in storage.
     */
    public function meta(Project $project, Import $import, $index, $slug)
    {
        abort_unless($project->fields()->where('meta', true)->where('slug', $slug)->first(), 400);

        return $this->update($project, $import, $index, "meta->" . $slug);
    }

    /**
     * Update the specified resource in storage.
     */
    public function category(Project $project, Import $import, $index, Category $category)
    {
        abort_unless($project->id == $category->project_id, 400);

        return $this->update($project, $import, $index, "category->" . $category->id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function thread(Project $project, Import $import, $index, Thread $thread)
    {
        abort_unless($project->id == $thread->project_id, 400);

        return $this->update($project, $import, $index, "thread->" . $thread->id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function event(Project $project, Import $import, $index)
    {
        return $this->update($project, $import, $index, "events");
    }

    /**
     * Update the specified resource in storage.
     */
    public function order(Project $project, Import $import, $index)
    {
        return $this->update($project, $import, $index, "orders");
    }

    /**
     * Update the specified resource in storage.
     */
    public function sms(Project $project, Import $import, $index)
    {
        return $this->update($project, $import, $index, "sms");
    }

    /**
     * Update the specified resource in storage.
     */
    public function interaction(Project $project, Import $import, $index)
    {
        return $this->update($project, $import, $index, "interactions");
    }

    /**
     * Update the specified resource in storage.
     */
    public function link(Project $project, Import $import, $index)
    {
        return $this->update($project, $import, $index, "links");
    }

    /**
     * Update the specified resource in storage.
     */
    public function user(Project $project, Import $import, $index)
    {
        return $this->update($project, $import, $index, "users");
    }

    /**
     * Update the specified resource in storage.
     */
    protected function update(Project $project, Import $import, $index, $field)
    {
        abort_unless($project->id == $import->project_id, 404);
        abort_unless(count($import->headers ?: []) > $index, 400);
        abort_if($import->is_processing, 404);

        $headers = $import->headers ?: [];
        $mapping = $import->mapping ?: array_fill(0, count($headers), null);

        $overflow = count($headers) > count($mapping);
        if ($overflow > 0) {
            $mapping = array_merge($mapping, array_fill(0, $overflow, null));
        }

        $mapping[$index] = $field;
        
        $import->update([
            'mapping' => array_values($mapping)
        ]);

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Import $import, $index)
    {
        abort_unless($project->id == $import->project_id, 404);
        abort_unless(count($import->mapping ?: []) > $index, 400);
        abort_if($import->is_processing, 404);

        $mapping = $import->mapping;
        $mapping[$index] = null;
        $import->mapping = $mapping;
        $import->save();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
