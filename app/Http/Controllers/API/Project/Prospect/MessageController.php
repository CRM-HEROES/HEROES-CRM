<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\Thread;
use App\Utils\ProjectMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{
    /**
     * Send a direct email to a prospect using the project Brevo settings.
     */
    public function email(Request $request, Project $project, Prospect $prospect)
    {
        abort_unless($project->id == $prospect->project_id, 404);

        $this->validate($request, array_merge($this->emailRules(), [
            'to' => 'required|email',
        ]));
        $attachments = $this->emailAttachments($request, $project);

        abort_unless(ProjectMail::configure($project), 422, trans('email.error.empty_setting'));

        $this->sendDirectEmail($request, $project, $prospect, $request->input('to'), $attachments);

        return ['sent' => true];
    }

    /**
     * Send one direct email per selected prospect.
     */
    public function bulkEmail(Request $request, Project $project)
    {
        $this->validate($request, array_merge($this->emailRules(), [
            'prospects' => 'required|array|min:1',
            'prospects.*' => 'required|integer|distinct',
        ]));
        $attachments = $this->emailAttachments($request, $project);

        $prospects = $project->prospects()
            ->whereIn('id', $request->input('prospects'))
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        if ($prospects->isEmpty()) {
            throw ValidationException::withMessages([
                'prospects' => 'Aucun prospect sélectionné ne possède une adresse email dans ce projet.',
            ]);
        }

        abort_unless(ProjectMail::configure($project), 422, trans('email.error.empty_setting'));

        foreach ($prospects as $prospect) {
            $this->sendDirectEmail($request, $project, $prospect, $prospect->email, $attachments);
        }

        return ['sent' => $prospects->count()];
    }

    protected function emailRules(): array
    {
        return [
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'category' => 'nullable|string|max:255',
            'attachments' => 'sometimes|array|max:10',
            'attachments.*' => 'required|file|max:10240',
            'template_id' => 'nullable|integer|required_with:template_attachment_ids',
            'template_attachment_ids' => 'sometimes|array|max:10',
            'template_attachment_ids.*' => 'required|string|uuid|distinct',
        ];
    }

    /**
     * Resolve only server-owned template paths, never paths supplied by the client.
     */
    protected function emailAttachments(Request $request, Project $project): array
    {
        $uploads = $request->file('attachments', []);
        $ids = $request->input('template_attachment_ids', []);

        if (count($uploads) + count($ids) > 10) {
            throw ValidationException::withMessages([
                'attachments' => 'Vous pouvez joindre au maximum 10 fichiers.',
            ]);
        }

        $attachments = array_map(fn ($file) => [
            'path' => $file->getRealPath(),
            'name' => $file->getClientOriginalName(),
            'type' => $file->getMimeType(),
        ], $uploads);

        if (!$request->filled('template_id')) {
            return $attachments;
        }

        $template = MessageTemplate::query()
            ->where('project_id', $project->id)
            ->find($request->input('template_id'));

        if (!$template) {
            throw ValidationException::withMessages([
                'template_id' => 'Ce modèle de message est introuvable dans ce projet.',
            ]);
        }

        $storedFiles = collect($template->attachments ?? [])->keyBy('id');
        $disk = Storage::disk('local');
        foreach ($ids as $id) {
            $file = $storedFiles->get($id);
            if (!$file || !$disk->exists($file['path']) || $disk->size($file['path']) > 10 * 1024 * 1024) {
                throw ValidationException::withMessages([
                    'template_attachment_ids' => 'Une pièce jointe du modèle est introuvable ou dépasse 10 Mo. Rechargez le modèle.',
                ]);
            }
            $attachments[] = [
                'path' => $disk->path($file['path']),
                'name' => $file['name'],
                'type' => $file['type'],
            ];
        }

        return $attachments;
    }

    protected function sendDirectEmail(Request $request, Project $project, Prospect $prospect, string $to, array $attachments): void
    {
        $subject = $this->personalizeEmail($request->input('subject'), $prospect);
        Mail::send('emails.prospect-message', [
            'body' => $this->personalizeEmail($request->input('body'), $prospect),
            'category' => $request->input('category', 'Message'),
            'project' => $project,
            'subject' => $subject,
        ], function ($message) use ($to, $subject, $attachments) {
            $message->to($to)->subject($subject);
            foreach ($attachments as $file) {
                $message->attach($file['path'], ['as' => $file['name'], 'mime' => $file['type']]);
            }
        });
    }

    protected function personalizeEmail(string $text, Prospect $prospect): string
    {
        return preg_replace_callback('/\{prospect\.([\w.-]+)\}/u', function ($matches) use ($prospect) {
            $key = $matches[1];
            // Resolve stored fields only; tokens must not traverse model relationships.
            if (str_starts_with($key, 'meta.')) {
                $value = data_get($prospect->meta, substr($key, 5));
            } else {
                $value = array_key_exists($key, $prospect->getAttributes()) ? $prospect->getAttribute($key) : null;
            }

            return is_scalar($value) ? (string) $value : '';
        }, $text);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Prospect $prospect, Thread $thread)
    {
        // Thread associated to the current project
        abort_unless($project->id == $thread->project_id, 404);

        return $prospect
            ->messages()
            ->with('creator:id,name')
            ->with('users:id,name')
            ->with('attachments')
            ->where('thread_id', $thread->id)
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project, Prospect $prospect, Thread $thread)
    {
        // Thread associated to the current project
        abort_unless($project->id == $thread->project_id, 404);

        // See: App\Observers\MessageObserver:created
        $message = $prospect
            ->messages()
            ->create(array_merge($request->only('body'), [
                'thread_id' => $thread->id,
                'from_user' => 1
            ], [
                'creator_id' => auth()->id(),
            ]));

        /**
         * Invoice attachment
         */
        $this->attachInvoice($request, $project, $message);

        /**
         * Attachments
         */
        foreach ($this->storeFiles($request, $project) as $file) {
            $message->attachments()->create(array_merge($file, ['creator_id' => auth()->id()]));
        }

        /**
         * Waiting users
         */
        foreach ($project->users()->whereIn('id', $request->input('users', []))->get() as $user) {
            $message->users()->syncWithoutDetaching([$user->id => [
                'updated_at' => \Carbon\Carbon::now()
            ]]);
        }

        $message->load('creator:id,name');
        $message->load('users:id,name');
        $message->load('attachments');

        return $message;
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Prospect $prospect, Thread $thread, Message $message)
    {
        // Thread associated to the current project
        abort_unless($project->id == $thread->project_id, 404);
        // Prospect associated to the message
        abort_unless($prospect->id == $message->prospect_id, 404);
        // Thread associated to the message
        abort_unless($thread->id == $message->thread_id, 404);

        return $message;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Prospect $prospect, Thread $thread, Message $message)
    {
        // Thread associated to the current project
        abort_unless($project->id == $thread->project_id, 404);
        // Prospect associated to the message
        abort_unless($prospect->id == $message->prospect_id, 404);
        // Thread associated to the message
        abort_unless($thread->id == $message->thread_id, 404);

        $message->update($request->only(
            'body'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Prospect $prospect, Thread $thread, Message $message)
    {
        // Thread associated to the current project
        abort_unless($project->id == $thread->project_id, 404);
        // Prospect associated to the message
        abort_unless($prospect->id == $message->prospect_id, 404);
        // Thread associated to the message
        abort_unless($thread->id == $message->thread_id, 404);

        $message->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }

    /**
     * Send message to multiple prospects.
     */
    public function bulk(Request $request, Project $project, Prospect $prospect, Thread $thread)
    {
        // Thread associated to the current project
        abort_unless($project->id == $thread->project_id, 404);

        $this->validate($request, [
            'prospects' => 'required',
        ]);

        $prospects = $project
            ->prospects()
            ->whereIn('id', $request->input('prospects'))
            ->get();

        foreach ($prospects as $prospect) {
            $prospect
                ->messages()
                ->create(array_merge($request->only('body'), [
                    'thread_id' => $thread->id,
                    'from_user' => 1
                ], [
                    'creator_id' => auth()->id(),
                ]));
        }

        return ['message' => "Message being sent to prospects"];
    }

    /**
     * Attach invoice to message
     */
    protected function attachInvoice(Request $request, Project $project, Message $message)
    {
        // Invoice given
        if (!$request->filled('invoice')) {
            return;
        }

        // Invoice order is associated
        // to the message prospect
        $invoice = Invoice::find($request->input('invoice'));
        if (!$message->prospect->orders()->where('id', $invoice->order_id)->first()) {
            return;
        }

        // Copy the invoice
        // to another folder
        $extension = pathinfo($invoice->path)['extension'];
        $name = Str::random(30) . '.' . $extension;
        $path = $project->slug . "/" . $name;
        Storage::disk('messages')->writeStream($path, Storage::disk('documents')->readStream($invoice->path));

        // Attach the copied file
        // to the message
        $message->attachments()->create([
            'path' => $path,
            'name' => $invoice->document->name . "." . $extension,
            'size' => Storage::disk('documents')->size($invoice->path),
            'creator_id' => auth()->id()
        ]);
    }

    /**
     * Store files
     */
    protected function storeFiles(Request $request, Project $project)
    {
        $attachments = $request->file('attachments', []);
        $files = [];

        foreach ($attachments as $attachment) {
            $originalName = $attachment->getClientOriginalName();
            $name = Str::random(30) . '.' . pathinfo($originalName)['extension'];

            $files[] = [
                'path' => $attachment->storeAs($project->slug, $name, 'messages'),
                'name' => $originalName,
                'size' => $attachment->getSize(),
            ];
        }

        return $files;
    }
}
