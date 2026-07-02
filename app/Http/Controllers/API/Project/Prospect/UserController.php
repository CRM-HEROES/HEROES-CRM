<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Events\ProspectUserAttached;
use App\Filters\ProspectFilters;
use App\Filters\ProspectRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;
use App\Models\Prospect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Prospect $prospect)
    {
        return $prospect
            ->users()
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Prospect $prospect, User $user)
    {
        // User validation:
        // @see App\Http\Middleware\ProjectUserCheck

        $prospect
            ->users()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$user->id => [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]]);

        ProspectUserAttached::dispatch($prospect, $user);

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Prospect $prospect, User $user)
    {
        // User validation:
        // @see App\Http\Middleware\ProjectUserCheck

        $prospect->users()->detach($user->id);

        return ['message' => trans('common.success.deleted_resource')];
    }
    
    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        $this->validate($request, [
            'users' => 'required',
            'filters' => 'required',
            'action' => 'required',
        ]);

        $skip = 0;
        $take = 1000;
        $prospectFilters = new ProspectFilters();
        $prospectFilters->addFilters($request->input('filters'));

        do {
            // Only users
            // associated to the current project
            $users = $project
                ->users()
                ->whereIn('id', $request->input('users'))
                ->get(['id']);

            // Only prospects
            // associated to the current project
            $prospects = $project
                ->prospects()
                ->filter($prospectFilters)
                ->whereNull('processed_at')
                ->get(['id']);
                
            switch ($request->input('action')) {
                case "attach":
                    $this->bulkActionAttach($prospects, $users);
                    return ['message' => trans('common.success.updated_resource')];            

                case "detach":
                    $this->bulkActionDetach($prospects, $users);
                    return ['message' => trans('common.success.deleted_resource')];

                default:
                    return response('Unknown bulk action.', 400);
            }
            
            $skip += $take;
        } while (count($prospects) >= $take);
    }

    /**
     * Bulk attach users
     */
    protected function bulkActionAttach(&$prospects, &$users) {
        // Remove previous data
        $this->bulkActionDetach($prospects, $users);
        
        $prospectsIds = $prospects->map(function($prospect) {
            return $prospect->id;
        })
        ->toArray();

        $usersIds = $users->map(function($user) {
            return $user->id;
        })
        ->toArray();
            
        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($user) use($prospectsIds, $now) {
            return array_map(function($prospect) use($user, $now) {
                return [
                    'user_id' => $user,
                    'prospect_id' => $prospect,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $prospectsIds);
        }, $usersIds);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        // Add new data
        DB::table('prospect_user')->insert($data);

        foreach ($prospects as $prospect) {
            foreach ($users as $user) {
                ProspectUserAttached::dispatch($prospect, $user);        
            }
        }
    }

    /**
     * Bulk detach users
     */
    protected function bulkActionDetach(&$prospects, &$users) {
        $prospectsIds = $prospects->map(function($prospect) {
            return $prospect->id;
        })
        ->toArray();

        $usersIds = $users->map(function($user) {
            return $user->id;
        })
        ->toArray();
            
        DB::table('prospect_user')
            ->whereIn('user_id', $usersIds)
            ->whereIn('prospect_id', $prospectsIds)
            ->delete();
    }
}
