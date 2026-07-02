<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Support\Facades\File;

class DocumentTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        $templates = config("document.templates");

        return collect(array_values($templates))->map(function($template) {
            return collect($template)->only('key', 'name', 'description', 'thumbnail');
        });
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Project $project, $template)
    {
        abort_unless($t = config("document.templates." . $template, null), 404);

        $document = $project
            ->documents()
            ->create([
                'name' => $t['name'],
                'description' => $t['description'],
                'for' => $t['for'],
                'creator_id' => auth()->id(),
            ]);

        $contents = File::get(base_path('app/Utils/Document/Templates/' . $template . '.json'));
        $t = json_decode($contents, true);

        foreach ($t["pages"] as $i => $page) {
            $documentPage = $document->pages()->create([
                "page" => $i + 1,
                "width" => $page["width"],
                "height" => $page["height"],
            ]);

            $fields = array_map(function($field) use($documentPage) {
                return collect(array_merge($field, [
                    "page_id" => $documentPage->id
                ]))
                ->only('type', 'content', 'style', 'page_id')
                ->toArray();
            }, $page["fields"]);
    
            $document->fields()->createMany($fields);    
        }

        return $document;
    }
}
