<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MessageTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return $project->messageTemplates;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('projectMessage-templateAdd', $project), 404);

        $this->validateTemplate($request);
        $attachments = $this->storeAttachments($request, $project);

        try {
            return $project->messageTemplates()->create(array_merge(
                $request->only('name', 'body', 'subject'),
                ['creator_id' => auth()->id(), 'attachments' => $attachments]
            ));
        } catch (\Throwable $exception) {
            $this->deleteAttachments($attachments);
            throw $exception;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, MessageTemplate $messageTemplate)
    {
        abort_unless($project->id == $messageTemplate->project_id, 404);

        return $messageTemplate;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, MessageTemplate $messageTemplate)
    {
        abort_unless(auth()->user()->can('projectMessage-templateUpdate', $project), 404);
        abort_unless($project->id == $messageTemplate->project_id, 404);

        $existing = $messageTemplate->attachments ?? [];
        $this->validateTemplate($request, $existing, true);
        $retained = $request->exists('retained_attachment_ids')
            ? array_values(array_filter($existing, fn ($attachment) => in_array(
                $attachment['id'], $request->input('retained_attachment_ids') ?? [], true
            )))
            : $existing;
        $uploads = $request->file('attachments', []);
        if (count($retained) + count($uploads) > 10) {
            throw ValidationException::withMessages(['attachments' => 'Vous pouvez joindre au maximum 10 fichiers.']);
        }

        $added = $this->storeAttachments($request, $project);
        try {
            $messageTemplate->update(array_merge(
                $request->only('name', 'body', 'subject', 'order'),
                ['attachments' => array_merge($retained, $added)]
            ));
        } catch (\Throwable $exception) {
            $this->deleteAttachments($added);
            throw $exception;
        }
        $this->deleteAttachments(array_filter($existing, fn ($attachment) => !in_array(
            $attachment['id'], array_column($retained, 'id'), true
        )));

        return $messageTemplate->fresh();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, MessageTemplate $messageTemplate)
    {
        abort_unless(auth()->user()->can('projectMessage-templateDelete', $project), 404);
        abort_unless($project->id == $messageTemplate->project_id, 404);

        $attachments = $messageTemplate->attachments ?? [];
        $messageTemplate->delete();
        $this->deleteAttachments($attachments);

        return ['message' => trans('common.success.deleted_resource')];
    }

    protected function validateTemplate(Request $request, array $existing = [], bool $updating = false): void
    {
        $this->validate($request, [
            'name' => ($updating ? 'sometimes|' : '').'required|string|max:255',
            'body' => ($updating ? 'sometimes|' : '').'required|string',
            'subject' => 'nullable|string|max:255',
            'order' => 'sometimes|integer|min:1',
            'attachments' => 'sometimes|array|max:10',
            'attachments.*' => 'file|max:10240',
            'retained_attachment_ids' => 'nullable|array|max:10',
            'retained_attachment_ids.*' => ['string', 'distinct', Rule::in(array_column($existing, 'id'))],
        ]);
    }

    protected function storeAttachments(Request $request, Project $project): array
    {
        $attachments = [];
        try {
            foreach ($request->file('attachments', []) as $file) {
                $id = (string) Str::uuid();
                $path = $file->storeAs('message-templates/'.$project->id, $id, 'local');
                if ($path === false) {
                    throw new \RuntimeException('Impossible de sauvegarder la pièce jointe.');
                }
                $attachments[] = [
                    'id' => $id,
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'type' => $file->getMimeType() ?: 'application/octet-stream',
                    'size' => $file->getSize(),
                ];
            }
        } catch (\Throwable $exception) {
            $this->deleteAttachments($attachments);
            throw $exception;
        }

        return $attachments;
    }

    protected function deleteAttachments(array $attachments): void
    {
        foreach ($attachments as $attachment) {
            Storage::disk('local')->delete($attachment['path']);
        }
    }
}
