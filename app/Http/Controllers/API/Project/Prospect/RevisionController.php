<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Prospect;
use Illuminate\Support\Str;

class RevisionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Prospect $prospect)
    {
        $fields = $project->fields()->where('for', 'prospect')->get();
        $categories = $project->categories()->where('for', 'prospect')->get();

        $revisions = $prospect->revisionHistory()
            ->latest()
            ->where(function($query) use($fields, $categories) {
                $query
                    ->whereIn('key', $fields->map(function($field) {
                        return ($field->meta ? "meta->" : "") . $field->slug;
                    }))
                    ->orWhereIn('key', $categories->map(function($category) {
                        return "category->" . $category->id;
                    }));
            })
            ->paginate(25);

        $revisions->getCollection()->transform(function ($item, $key) use($fields, $categories) {
            $item->user = $item->userResponsible() ? $item->userResponsible()->only('id', 'name') : null;
            
            if (Str::startsWith($item->fieldName(), 'category->')) {
                $item->field = $categories
                    ->where('id', Str::replace('category->', '', $item->fieldName()))
                    ->first()
                    ->name;
                $item->type = 'label';
            } else {
                $item->field = $fields
                    ->where('slug', Str::replace('meta->', '', $item->fieldName()))
                    ->where('meta', Str::startsWith($item->fieldName(), 'meta->'))
                    ->first()
                    ->name;
                $item->type = 'field';
            }

            $item->date = \Carbon\Carbon::parse($item->created_at)->diffForHumans();

            return $item;
        });

        return $revisions;
    }

}
