<?php

namespace App\Http\Controllers\API;

use App\Filters\ProjectRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    protected $fields = [
        'capital',
        'city',
        'country', 
        'email',
        'naf',
        'name',
        'num_tva_intra',
        'phone_number',
        'postal_code',
        'siret',
        'slug',
        'street',
        'street_bis',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ProjectRequestFilters $filters)
    {
        return $this->getProjects(
            $request, 
            $filters
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // abort_unless(auth()->user()->is_super_admin, 404);
        
        $this->validate($request, [
            'name' => 'required'
        ]);

        return Project::create(array_merge($request->only(
            $this->fields
        ), [
            'job_category' => $request->input('job_category', null),
            'creator_id' => auth()->id(),
        ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $project->load([
            'calendars' => function($query) {
                $query
                    ->select('id', 'project_id', 'name', 'color', 'bgcolor', 'type', 'relevance')
                    ->orderBy('relevance', 'desc')
                    ->orderBy('name');
            },
            'categories' => function($query) {
                $query
                    ->select('id', 'project_id', 'name', 'color', 'bgcolor', 'for', 'relevance', 'unique')
                    // ->orderBy('relevance', 'desc')
                    ->orderBy('order')
                    ->orderBy('name');
            },
            'categories.labels' => function($query) {
                $query
                    ->select('id', 'category_id', 'name', 'color', 'bgcolor', 'relevance')
                    ->whereNull('archived_at')
                    // ->orderBy('relevance', 'desc')
                    ->orderBy('order')
                    ->orderBy('name');
            },
            'documents' => function($query) {
                $query->select('id', 'project_id', 'name', 'for', 'folder_id');
            },
            'fields' => function($query) {
                $query->select('id', 'project_id', 'name', 'for', 'slug', 'type', 'default_value', 'required', 'searchable', 'format', 'unique', 'meta', 'order');
            },
            'folders' => function($query) {
                $query
                    ->select('id', 'project_id', 'name', 'color', 'bgcolor', 'for', 'private', 'relevance')
                    ->orderBy('relevance', 'desc')
                    ->orderBy('name');
            },
            'groups' => function($query) {
                $query
                    ->select('id', 'project_id', 'name', 'color', 'bgcolor', 'relevance')
                    ->orderBy('relevance', 'desc')
                    ->orderBy('name');
            },
            'imports' => function($query) {
            },
            'menus' => function($query) {
                $query
                    ->select('id', 'project_id', 'name', 'color', 'bgcolor', 'filters')
                    ->with('users', function($query) {
                        $query
                            ->select('id')
                            ->where('id', auth()->id())
                            ->withPivot('default');
                    });
            },
            'metrics' => function($query) {
                $query->select('id', 'project_id', 'name', 'color', 'filters');
            },
            'questionnaires' => function($query) {
                $query
                    ->select('id', 'project_id', 'name', 'relevance')
                    ->orderBy('relevance', 'desc')
                    ->orderBy('name');
            },
            'orderActions' => function($query) {
                $query
                    ->select('id', 'project_id', 'name', 'color', 'bgcolor', 'relevance')
                    ->orderBy('relevance', 'desc')
                    ->orderBy('name');
            },
            'orderStatuses' => function($query) {
                $query
                    ->select('id', 'project_id', 'name', 'color', 'bgcolor', 'relevance')
                    ->orderBy('relevance', 'desc')
                    ->orderBy('name');
            },
            'orderSteps' => function($query) {
                $query
                    ->select('id', 'project_id', 'name', 'color', 'bgcolor', 'relevance')
                    ->orderBy('relevance', 'desc')
                    ->orderBy('name');
            },
            'pipedriveAccounts' => function($query) {
                $query->select('id', 'project_id', 'name');
            },
            'pipelines' => function($query) {
                $query->select('id', 'project_id', 'name');
            },
            'pipelines.labels' => function($query) {
                $query
                    ->select('id', 'category_id', 'name', 'color', 'bgcolor')
                    ->orderBy('pipeline_label.order');
            },
            'products' => function($query) {
                $query
                    ->whereNull('archived_at')
                    ->orderBy('relevance', 'desc');
                // $query->select('id', 'project_id', 'name');
            },
            'products.images' => function($query) {
            },
            'roles' => function($query) {
                $query->select('id', 'project_id', 'name', 'users_count');
            },
            'threads' => function($query) {
                $query
                    ->select('id', 'project_id', 'name', 'color', 'bgcolor', 'private', 'relevance')
                    ->orderBy('relevance', 'desc')
                    ->orderBy('name');
            },
            'users' => function($query) {
                $query->select('id', 'name', 'last_name', 'email', 'role', 'users.creator_id')
                    ->forCurrentUser()
                    ->with("roles:id,name");
            },
            'userSettings' => function($query) {
                $query->select('id', 'user_id', 'project_id', 'key', 'value')->where('user_id', auth()->id());
            },
            'users.roles' => function($query) {
                $query->select('id', 'name');
            },
            'vehicles' => function($query) {
                $query->select('id', 'name', 'project_id', 'user_id');
            },
        ]);
        
        return $project;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('', $project), 404);

        $project->update($request->only(
            $this->fields
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        abort_unless(auth()->user()->can('', $project), 404);
        
        $project->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }

    /**
     * Update the specified resource in storage.
     */
    public function bulk(Request $request)
    {
        abort_unless(auth()->user()->is_super_admin, 404);

        $this->validate($request, [
            'projects' => 'required',
            'action' => 'required',
        ]);

        $projects = Project::whereIn('id', $request->input('projects'));
            // ->withTrashed();

        switch ($request->input('action')) {
            case "delete":
                $projects->delete();
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * 
     */
    public function orders(Request $request) 
    {
        $this->validate($request, [
            'orders' => 'required',
        ]);

        foreach ($request->input('orders') as $order) {
            $project = auth()->user()->projects->where('id', $order['project'])->first();

            if ($project) {
                $project->pivot->order = $order['order'];
                $project->pivot->update();
            }
        }

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Get events
     */
    protected function getProjects(Request $request, ProjectRequestFilters $filters)
    {
        // Count
        $count = min($request->input('count', 100), 500);

        // Sort By
        $sortBy = $request->input('sortBy', "id");
        if (!in_array($sortBy, $this->fields)) {
            $sortBy = "id";
        }

        // Sort Order
        $sortOrder = $request->input('sortOrder', "") == "desc" ? "desc" : "asc";

        // Result
        return Project::filter($filters)
            ->with(["activeUsers" => function($query) {
                $query->select("id", "name", "last_project_id")->where("last_activity", ">=", \Carbon\Carbon::now()->subMinutes(5));
            }])
            ->orderBy($sortBy, $sortOrder)
            ->paginate($count);
    }
}
