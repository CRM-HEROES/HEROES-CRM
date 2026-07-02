<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\Sms;
use Illuminate\Http\Request;

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
            ->with('creator:id,name')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Send new SMS
     */
    public function store(Request $request, Project $project, Prospect $prospect)
    {
        abort_unless(auth()->user()->can('prospectSmsAdd', $project), 404);

        // See: App\Observers\SmsObserver:created
        // To view which API we should use
        // to send the SMS
        return $prospect
            ->sms()
            ->create(array_merge($request->only(
                'message', 
                'source'
            ), [
                'from_user' => 1,
                'creator_id' => auth()->id(),
            ]));
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
            'prospects' => 'required',
        ]);

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

        foreach ($prospects as $prospect) {
            $prospect
                ->sms()
                ->create(array_merge($request->only(
                    'message', 
                    'source'
                ), [
                    'from_user' => 1,
                    'creator_id' => auth()->id(),
                ]));
        }
    }
}
