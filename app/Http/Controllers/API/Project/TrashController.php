<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\File;
use App\Models\Label;
use App\Models\Message;
use App\Models\Order;
use App\Models\Project;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    /**
     * Prospects
     */
    public function prospect(Project $project, Request $request)
    {
        return $project
            ->prospects()
            ->select('id', 'first_name', 'last_name', 'deleted_at')
            ->onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(100);
    }
    
    /**
     * Categories
     */
    public function category(Project $project, Request $request)
    {
        return $project
            ->categories()
            ->select('id', 'name', 'color', 'bgcolor', 'deleted_at')
            ->onlyTrashed()
            ->when($request->has('query'), function($builder) use($request) {
                $query = str_replace('%', '', $request->input('query'));
                $builder->where('name', 'LIKE', "%${query}%");
            })
            ->orderBy('deleted_at', 'desc')
            ->paginate(100);
    }
    
    /**
     * Labels
     */
    public function label(Project $project, Request $request)
    {
        return Label
            ::whereHas('category', function($query) use($project) {
                $query->where('project_id', $project->id);
            })
            ->select('id', 'name', 'color', 'bgcolor', 'category_id', 'deleted_at')
            ->with('category:id,name,color,bgcolor')
            ->onlyTrashed()
            ->when($request->has('query'), function($builder) use($request) {
                $query = str_replace('%', '', $request->input('query'));
                $builder->where('name', 'LIKE', "%${query}%");
            })
            ->orderBy('deleted_at', 'desc')
            ->paginate(100);
    }
    
    /**
     * Threads
     */
    public function thread(Project $project, Request $request)
    {
        return $project
            ->threads()
            ->select('id', 'name', 'color', 'bgcolor', 'deleted_at')
            ->onlyTrashed()
            ->when($request->has('query'), function($builder) use($request) {
                $query = str_replace('%', '', $request->input('query'));
                $builder->where('name', 'LIKE', "%${query}%");
            })
            ->orderBy('deleted_at', 'desc')
            ->paginate(100);
    }
    
    /**
     * Messages
     */
    public function message(Project $project, Request $request)
    {
        return Message
            ::whereHas('thread', function($query) use($project) {
                $query->where('project_id', $project->id);
            })
            ->select('id', 'plain_text', 'thread_id', 'prospect_id', 'sent', 'error', 'deleted_at')
            ->with('thread:id,name,color,bgcolor')
            ->with('prospect:id,first_name,last_name')
            ->with('creator:id,name')
            ->with('users:id,name')
            ->with('attachments')
            ->onlyTrashed()
            ->when($request->has('query'), function($builder) use($request) {
                $query = str_replace('%', '', $request->input('query'));
                $builder->where('body', 'LIKE', "%${query}%");
            })
            ->orderBy('deleted_at', 'desc')
            ->paginate(100);
    }
    
    /**
     * Folders
     */
    public function folder(Project $project, Request $request)
    {
        return $project
            ->folders()
            ->select('id', 'name', 'color', 'bgcolor', 'deleted_at')
            ->onlyTrashed()
            ->when($request->has('query'), function($builder) use($request) {
                $query = str_replace('%', '', $request->input('query'));
                $builder->where('name', 'LIKE', "%${query}%");
            })
            ->orderBy('deleted_at', 'desc')
            ->paginate(100);
    }
    
    /**
     * Files
     */
    public function file(Project $project, Request $request)
    {
        return File
            ::whereHas('folder', function($query) use($project) {
                $query->where('project_id', $project->id);
            })
            ->select('id', 'name', 'folder_id', 'prospect_id', 'deleted_at')
            ->with([
                'folder' => function($query) {
                    $query->select('id', 'name', 'color', 'bgcolor', 'project_id');
                },
                'folder.project' => function($query) {
                    $query->select('id', 'slug');
                },
            ])
            ->with('prospect:id,first_name,last_name')
            ->onlyTrashed()
            ->when($request->has('query'), function($builder) use($request) {
                $query = str_replace('%', '', $request->input('query'));
                $builder->where('name', 'LIKE', "%${query}%");
            })
            ->orderBy('deleted_at', 'desc')
            ->paginate(100);
    }

    /**
     * Order actions
     */
    public function orderAction(Project $project, Request $request)
    {
        return $project
            ->orderActions()
            ->select('id', 'name', 'color', 'bgcolor', 'deleted_at')
            ->onlyTrashed()
            ->when($request->has('query'), function($builder) use($request) {
                $query = str_replace('%', '', $request->input('query'));
                $builder->where('name', 'LIKE', "%${query}%");
            })
            ->orderBy('deleted_at', 'desc')
            ->paginate(100);
    }
    
    /**
     * Order statuses
     */
    public function orderStatus(Project $project, Request $request)
    {
        return $project
            ->orderActions()
            ->select('id', 'name', 'color', 'bgcolor', 'deleted_at')
            ->onlyTrashed()
            ->when($request->has('query'), function($builder) use($request) {
                $query = str_replace('%', '', $request->input('query'));
                $builder->where('name', 'LIKE', "%${query}%");
            })
            ->orderBy('deleted_at', 'desc')
            ->paginate(100);
    }
    
    /**
     * Orders
     */
    public function order(Project $project, Request $request)
    {
        return Order
            ::whereHas('prospect', function($query) use($project) {
                $query->where('project_id', $project->id);
            })
            ->with([
                'products' => function($query) {},
                'products.images' => function($query) {
                    $query->orderBy('order', 'asc');
                }
            ])
            ->with('prospect:id,first_name,last_name')
            ->with('steps:id,name')
            ->with('actions:id,name')
            ->with('status:id,name')
            ->with('creator:id,name')
            ->onlyTrashed()
            ->when($request->has('query'), function($builder) use($request) {
                $query = str_replace('%', '', $request->input('query'));
                $builder->where('name', 'LIKE', "%${query}%");
            })
            ->orderBy('deleted_at', 'desc')
            ->paginate(100);
    }

    /**
     * Calendars
     */
    public function calendar(Project $project, Request $request)
    {
        return $project
            ->calendars()
            ->select('id', 'name', 'color', 'bgcolor', 'deleted_at')
            ->onlyTrashed()
            ->when($request->has('query'), function($builder) use($request) {
                $query = str_replace('%', '', $request->input('query'));
                $builder->where('name', 'LIKE', "%${query}%");
            })
            ->orderBy('deleted_at', 'desc')
            ->paginate(100);
    }
    
    /**
     * Events
     */
    public function event(Project $project, Request $request)
    {
        return Event
            ::whereHas('prospect', function($query) use($project) {
                $query->where('project_id', $project->id);
            })
            ->select('id', 'name', 'deleted_at', 'started_at', 'prospect_id', 'calendar_id')
            ->with('prospect:id,first_name,last_name')
            ->with('calendar:id,name,color,bgcolor')
            ->onlyTrashed()
            ->when($request->has('query'), function($builder) use($request) {
                $query = str_replace('%', '', $request->input('query'));
                $builder->where('name', 'LIKE', "%${query}%");
            })
            ->orderBy('deleted_at', 'desc')
            ->paginate(100);
    }

    /**
     * 
     */
    protected function bulkItems(Project $project, $items, $type)
    {
        switch ($type) {
            case 'prospects':
                return $project
                    ->prospects()
                    ->whereIn('id', $items);
            case 'categories':
                return $project
                    ->categories()
                    ->whereIn('id', $items);
            case 'labels':
                return Label
                    ::whereHas('category', function($query) use($project) {
                        $query->where('project_id', $project->id);
                    })
                    ->whereIn('labels.id', $items);
            case 'threads':
                return $project
                    ->threads()
                    ->whereIn('id', $items);
            case 'messages':
                return Message
                    ::whereHas('thread', function($query) use($project) {
                        $query->where('project_id', $project->id);
                    })
                    ->whereIn('messages.id', $items);
            case 'folders':
                return $project
                    ->folders()
                    ->whereIn('id', $items);
            case 'files':
                return File
                    ::whereHas('folder', function($query) use($project) {
                        $query->where('project_id', $project->id);
                    })
                    ->whereIn('files.id', $items);
            case 'order-statuses':
                return $project
                    ->orderStatuses()
                    ->whereIn('id', $items);
            case 'order-actions':
                return $project
                    ->orderActions()
                    ->whereIn('id', $items);
            case 'orders':
                return Order
                    ::whereHas('prospect', function($query) use($project) {
                        $query->where('project_id', $project->id);
                    })
                    ->whereIn('orders.id', $items);
            case 'calendars':
                return $project
                    ->calendars()
                    ->whereIn('id', $items);
            case 'events':
                return Event
                    ::whereHas('calendar', function($query) use($project) {
                        $query->where('project_id', $project->id);
                    })
                    ->whereIn('events.id', $items);
            default:
                return null;
        }
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        $this->validate($request, [
            'action' => 'required',
        ]);

        $types = ['prospects', 'categories', 'labels', 'threads', 'messages', 'folders', 'files', 'order-actions', 'order-status', 'orders', 'calendars', 'events'];

        foreach ($types as $type) {
            if (!$request->has($type)) {
                continue;
            }

            if (empty($request->input($type))) {
                continue;
            }

            $items = $this->bulkItems($project, $request->input($type), $type)->onlyTrashed();

            switch ($request->input('action')) {
                case "delete":
                    $items->forceDelete();
                    return ['message' => trans('common.success.deleted_resource')];

                case "restore":
                    $items->restore();
                    return ['message' => trans('common.success.updated_resource')];
            }
        }
    }
}
