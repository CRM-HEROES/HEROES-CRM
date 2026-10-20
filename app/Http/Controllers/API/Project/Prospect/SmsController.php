<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\Sms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SmsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Prospect $prospect)
    {
        // abort_unless(auth()->user()->can('prospectSmsView', $project), 404);

        return $prospect
            ->sms()
            ->with(['creator:id,name', 'prospect.project:id,slug'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Send new SMS
     */
    public function store(Request $request, Project $project, Prospect $prospect)
    {
        abort_unless(auth()->user()->can('prospectSmsAdd', $project), 404);

        $this->validateSms($request);

        // See: App\Observers\SmsObserver:created
        // To view which API we should use
        // to send the SMS
        return $this->createSms($request, $project, $prospect);
    }

    public function attachment(Project $project, Prospect $prospect, Sms $sms, int $attachment)
    {
        abort_unless($prospect->id == $sms->prospect_id, 404);
        $media = ($sms->media ?? [])[$attachment] ?? null;
        abort_unless($media && isset($media['path']), 404);

        $disk = Storage::disk('sms');
        abort_unless($disk->exists($media['path']), 404);

        return $disk->response($media['path'], $media['file_name'], [
            'Content-Type' => $media['mime_type'],
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    /**
     * Remove SMS
     */
    public function destroy(Project $project, Prospect $prospect, Sms $sms)
    {
        // Prospect associated to the sms
        abort_unless($prospect->id == $sms->prospect_id, 404);

        $sms->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
    
    /**
     * Send sms to multiple prospects.
     */
    public function bulk(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('prospectSmsAdd', $project), 404);

        $this->validate($request, [
            'prospects' => 'required|array|min:1',
            'prospects.*' => 'integer',
        ]);
        $this->validateSms($request);

        $prospects = $project
            ->prospects()
            ->whereIn('id', $request->input('prospects'))
            // For users that does not
            // have permissions to see all prospects
            // he can only send sms to prospects affected to him
            ->when(!auth()->user()->can('prospectView', $project), function($query) {
                $query->whereHas('users', function($query) {
                    $query->where('id', auth()->id());
                });
            })
            ->get();

        $firstSms = null;
        foreach ($prospects as $prospect) {
            $sms = $this->createSms($request, $project, $prospect);
            $firstSms ??= $sms;
        }

        return ['count' => $prospects->count(), 'attachments' => $firstSms?->attachments ?? []];
    }

    protected function validateSms(Request $request): void
    {
        $this->validate($request, [
            'source' => 'required|string',
            'message' => $request->input('source') === 'cloudtalk'
                ? 'nullable|string|required_without:attachments' : 'nullable|string',
            'attachments' => [
                'nullable', 'array', 'max:5',
                Rule::prohibitedIf($request->input('source') !== 'cloudtalk'),
            ],
            'attachments.*' => 'required|file|mimes:jpg,jpeg,png,gif|max:5120',
        ]);

        $size = array_sum(array_map(fn ($file) => $file->getSize(), $request->file('attachments', [])));
        if ($size > 5 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'attachments' => trans('sms.cloudtalk.attachments_size'),
            ]);
        }
    }

    protected function createSms(Request $request, Project $project, Prospect $prospect): Sms
    {
        $media = [];
        try {
            foreach ($request->file('attachments', []) as $file) {
                $media[] = [
                    'path' => $file->store($project->id . '/' . $prospect->id, 'sms'),
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'extension' => $file->guessExtension(),
                ];
            }

            return $prospect->sms()->create(array_merge([
                'message' => (string) $request->input('message', ''),
                'source' => $request->input('source'),
                'from_user' => 1,
                'creator_id' => auth()->id(),
            ], $media ? ['media' => $media] : []))->setRelation('prospect', $prospect);
        } catch (\Throwable $e) {
            Storage::disk('sms')->delete(array_column($media, 'path'));
            throw $e;
        }
    }
}
