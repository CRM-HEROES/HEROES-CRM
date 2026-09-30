<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Message;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\Thread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MessageController extends Controller
{
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
