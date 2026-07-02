<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Utils\Project\LogoThumbnail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LogoController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $thumbnail = (new LogoThumbnail($project))->generate();

        if (!$thumbnail) {
            return response('Cannot generate thumbnail.', 400);
        }

        // Show thumbnail
        return response($thumbnail)->withHeaders([
            'Content-Type' => "image/jpeg",
            'Cache-Control' => 'public, max-age=31536000',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        $this->validate($request, [
            'file' => 'required'
        ]);

        $project->update($this->storeFile($request, $project));
        
        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Store file
     */
    protected function storeFile(Request $request, Project $project)
    {
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $name = Str::random(30) . '.' . pathinfo($originalName)['extension'];

        return [
            'logo' => $file->storeAs($project->slug, $name, 'logos')
        ];
    }
}
