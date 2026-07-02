<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Folder;
use App\Models\Project;
use App\Models\Prospect;
use App\Utils\File\FileThumbnail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Prospect $prospect, Folder $folder)
    {
        // Folder associated to the current project
        abort_unless($project->id == $folder->project_id, 404);
        // Folder for prospect
        abort_unless($folder->for == "prospect", 404);

        $folderSharedWithCurentUser = $folder->users()->where('id', auth()->id())->exists();

        return $prospect
            ->files()
            ->with('creator:id,name')
            ->where('folder_id', $folder->id)
            ->when(!$folderSharedWithCurentUser, fn($q) => $q->whereHas('users', fn($q) => $q->where('users.id', auth()->user()->id)))
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project, Prospect $prospect, Folder $folder)
    {
        // Folder associated to the current project
        abort_unless($project->id == $folder->project_id, 404);
        // Folder for prospect
        abort_unless($folder->for == "prospect", 404);

        $this->validate($request, [
            'file' => 'required'
        ]);

        // See: App\Observers\FileObserver:created
        // For Google Drive
        return $prospect
            ->files()
            ->create(array_merge($request->only(
                    'source'
                ), [
                    'folder_id' => $folder->id,
                    'from_user' => 1,
                    'creator_id' => auth()->id(),
                ],
                // Store file to the local storage
                $this->storeFile($request, $project, $prospect, $folder)
            ));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Prospect $prospect, Folder $folder, File $file)
    {
        // Folder associated to the current project
        abort_unless($project->id == $folder->project_id, 404);
        // Folder for prospect
        abort_unless($folder->for == "prospect", 404);
        // Prospect associated to the file
        abort_unless($prospect->id == $file->prospect_id, 404);
        // Folder associated to the file
        abort_unless($folder->id == $file->folder_id, 404);

        $disk = Storage::disk('folders');

        abort_unless($disk->exists($file->path), 404);

        return response($disk->get($file->path))
            ->header('Content-Type', $disk->mimeType($file->path))
            ->header('Content-Disposition', 'inline; filename="' . $file->name . '"');
    }

    /**
     * Display file thumbnail
     * Need to install
     * - Imagick
     * - GhostScript
     * To enable to generate thumbnail
     */
    public function thumbnail(Project $project, Prospect $prospect, Folder $folder, File $file)
    {
        // Folder associated to the current project
        abort_unless($project->id == $folder->project_id, 404);
        // Folder for prospect
        abort_unless($folder->for == "prospect", 404);
        // Prospect associated to the file
        abort_unless($prospect->id == $file->prospect_id, 404);
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
     * Display associated users.
     */
    public function users(Project $project, Prospect $prospect, Folder $folder, File $file)
    {
        // Folder associated to the current project
        abort_unless($project->id == $folder->project_id, 404);
        // Prospect associated to the file
        abort_unless($prospect->id == $file->prospect_id, 404);
        // Folder associated to the file
        abort_unless($folder->id == $file->folder_id, 404);
        //Creator of the file
        abort_unless($file->creator_id == auth()->user()->id, 404);

        return $file
            ->users()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Prospect $prospect, Folder $folder, File $file)
    {
        // Folder associated to the current project
        abort_unless($project->id == $folder->project_id, 404);
        // Prospect associated to the file
        abort_unless($prospect->id == $file->prospect_id, 404);
        // Folder associated to the file
        abort_unless($folder->id == $file->folder_id, 404);

        $file->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }

    /**
     * Store file
     */
    protected function storeFile(Request $request, Project $project, Prospect $prospect, Folder $folder)
    {
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $name = Str::random(30) . '.' . pathinfo($originalName)['extension'];

        return [
            'path' => $file->storeAs($project->slug . "/" . $prospect->id . "/" . $folder->id, $name, 'folders'),
            'name' => $originalName,
            'size' => $file->getSize()
        ];
    }


    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project, Prospect $prospect, Folder $folder)
    {
        $this->validate($request, [
            'files' => 'required',
            'users' => 'required',
            'action' => 'required',
        ]);

        // Only files
        // associated to the current folder
        $files = $folder
            ->files()
            ->whereIn('id', $request->input('files'))
            ->get(['id'])
            ->map(function($file) {
                return $file->id;
            })
            ->toArray();

        // Only users
        // associated to the current project
        $users = $project
            ->users()
            ->whereIn('id', $request->input('users'))
            ->get(['id'])
            ->map(function($user) {
                return $user->id;
            })
            ->toArray();

        switch ($request->input('action')) {
            case "attach":
                $this->bulkActionAttach($users, $files);
                return ['message' => trans('common.success.updated_resource')];

            case "detach":
                $this->bulkActionDetach($users, $files);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach files
     */
    protected function bulkActionAttach(&$users, &$files) {
        // Remove previous data
        $this->bulkActionDetach($users, $files);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($file) use($users, $now) {
            return array_map(function($user) use($file, $now) {
                return [
                    'file_id' => $file,
                    'user_id' => $user,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $users);
        }, $files);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('user_file')->insert($data);
    }

    /**
     * Bulk detach folders
     */
    protected function bulkActionDetach(&$users, &$files) {
        DB::table('user_file')
            ->whereIn('file_id', $files)
            ->whereIn('user_id', $users)
            ->delete();
    }
}
