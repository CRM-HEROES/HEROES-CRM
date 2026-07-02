<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\UserSetting;
use App\Utils\MenuIcon\ImageThumbnail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuIconController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project, $menu)
    {
        $this->validate($request, [
            'file' => 'required'
        ]);

        $this->storeFile($request, $project, $menu);
        
        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, $menu)
    {
        $userSetting = UserSetting::where([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'key' => 'menu-icon.' . $menu,
        ])
        ->first();

        abort_unless($userSetting, 404);

        $disk = Storage::disk('menu-icons');

        $path = $project->slug . "/" . auth()->id() . "/" . $menu . ".png";

        return response($disk->get($path))->withHeaders([
            'Content-Type' => $disk->mimeType($path),
            'Cache-Control' => 'public, max-age=31536000',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, $menu)
    {
        $userSetting = UserSetting::where([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'key' => 'menu-icon.' . $menu,
        ])
        ->first();

        abort_unless($userSetting, 404);

        $disk = Storage::disk('menu-icons');
        $path = $disk->path($project->slug . "/" . auth()->id() . "/" . $menu . ".png");

        $disk->delete($path);

        UserSetting
            ::where([
                'project_id' => $project->id,
                'user_id' => auth()->id(),
                'key' => 'menu-icon.' . $menu,
            ])->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }

    /**
     * Store file
     */
    protected function storeFile(Request $request, Project $project, $menu)
    {
        $disk = Storage::disk('menu-icons');
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $folder = $project->slug . "/" . auth()->id();
        $name = $menu . '.png';

        $filepath = $file->storeAs($folder, $name, 'menu-icons');
        $diskFilepath = $disk->path($filepath);

        $imagick = ImageThumbnail::generate($diskFilepath, 100);
        $imagick->writeImage($diskFilepath);

        UserSetting::updateOrCreate([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'key' => 'menu-icon.' . $menu,
        ], [
            'value' => \Carbon\Carbon::now()->format("Ymdhis"),
            'creator_id' => auth()->id(),
        ]);
    }
}
