<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Models\UserFile;
use App\Models\Folder;
use App\Models\Project;
use App\Models\User;
use App\Utils\File\FileThumbnail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, User $user, Folder $folder)
    {
        // User can access the file
        abort_unless(auth()->user()->can('', $project) || auth()->id() == $user->id, 404);
        // Folder associated to the current project
        abort_unless($project->id == $folder->project_id, 404);
        abort_unless($folder->for == "user", 404);

        return $user
            ->files()
            ->with('creator:id,name')
            ->where('folder_id', $folder->id)
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project, User $user, Folder $folder)
    {
        // User can access the file
        abort_unless(auth()->user()->can('', $project) || auth()->id() == $user->id, 404);
        // Folder associated to the current project
        abort_unless($project->id == $folder->project_id, 404);
        abort_unless($folder->for == "user", 404);

        $this->validate($request, [
            'file' => 'required'
        ]);

        // See: App\Observers\UserFileObserver:created
        // For Google Drive
        return $user
            ->files()
            ->create(array_merge($request->only(
                    'source'
                ), [
                    'folder_id' => $folder->id,
                    'creator_id' => auth()->id(),
                ], 
                // Store file to the local storage
                $this->storeFile($request, $project, $user, $folder)
            ));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, User $user, Folder $folder, UserFile $file)
    {
        // User can access the file
        abort_unless(auth()->user()->can('', $project) || auth()->id() == $user->id, 404);
        // Folder associated to the current project
        abort_unless($project->id == $folder->project_id, 404);
        // Folder for user
        abort_unless($folder->for == "user", 404);
        // User associated to the file
        abort_unless($user->id == $file->user_id, 404);
        // Folder associated to the file
        abort_unless($folder->id == $file->folder_id, 404);

        $disk = Storage::disk('folders');
        
        abort_unless($disk->exists($file->path), 404);

        return response($disk->get($file->path))
            ->header('Content-Type', $disk->mimeType($file->path))
            ->header('Content-Disposition', 'attachment; filename="' . $file->name . '"');
    }

    /**
     * Display file thumbnail
     * Need to install
     * - Imagick
     * - GhostScript
     * To enable to generate thumbnail
     */
    public function thumbnail(Project $project, User $user, Folder $folder, UserFile $file)
    {
        // User can access the file
        abort_unless(auth()->user()->can('', $project) || auth()->id() == $user->id, 404);
        // Folder associated to the current project
        abort_unless($project->id == $folder->project_id, 404);
        // Folder for user
        abort_unless($folder->for == "user", 404);
        // User associated to the file
        abort_unless($user->id == $file->user_id, 404);
        // Folder associated to the file
        abort_unless($folder->id == $file->folder_id, 404);

        // Generate thumbnail
        $thumbnail = (new FileThumbnail($file))->generate();
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
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, User $user, Folder $folder, UserFile $file)
    {
        // User can access the file
        abort_unless(auth()->user()->can('', $project) || auth()->id() == $user->id, 404);
        // Folder associated to the current project
        abort_unless($project->id == $folder->project_id, 404);
        // Folder for user
        abort_unless($folder->for == "user", 404);
        // User associated to the file
        abort_unless($user->id == $file->user_id, 404);
        // Folder associated to the file
        abort_unless($folder->id == $file->folder_id, 404);

        $file->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }

    /**
     * Store file
     */
    protected function storeFile(Request $request, Project $project, User $user, Folder $folder)
    {
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $name = Str::random(30) . '.' . pathinfo($originalName)['extension'];

        return [
            'path' => $file->storeAs($project->slug . "/" . $user->id . "/" . $folder->id, $name, 'folders'),
            'name' => $originalName,
            'size' => $file->getSize()
        ];
    }
}
