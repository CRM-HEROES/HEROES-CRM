<?php

namespace App\Http\Controllers\API\Project;

use App\Filters\OrderRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Project;
use App\Models\UserSetting;
use App\Utils\ProjectSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Project $project, OrderRequestFilters $orderRequestFilters)
    {
        // Count
        $count = min($request->input('count', 50), 500);

        // Sort By
        $sortBy = $request->input('sortBy', "id");
        
        if ($sortBy == "null") {
            $sortBy = null;
        } else {
            $sortBy = "created_at";
        }

        // Sort Order
        $sortOrder = $request->input('sortOrder', "desc") == "desc" ? "desc" : "asc";

        // Fields
        $fields = $this->getFields(
            $request, 
            $project
        );

        // Categories in which we select labels associated to prospects
        $categories = array_map(function($field) {
            return Str::replace('category->', '', $field);
        }, array_filter($fields, function($field) {
            return Str::startsWith($field, 'category->');
        }));

        return Order
            ::whereHas('prospect', function($query) use($project) {
                $query->where('project_id', $project->id);
            })

            // Prospect
            ->when(in_array('prospect', $fields), function($query) {
                $query->with([
                    'prospect' => function($query) {
                        $query->select('id', 'first_name', 'last_name');
                    },
                ]);
            })

            // Labels
            ->when(!empty($categories), function($query) use($categories) {
                $query->with(['prospect.labels' => function($query) use($categories) {
                    $query->whereIn('category_id', $categories)->select('id', 'name', 'category_id', 'color', 'bgcolor');
                }]);
            })

            // Status
            ->when(in_array('status', $fields), function($query) {
                $query->with([
                    'status' => function($query) {
                        $query->select('id', 'name', 'color', 'bgcolor');
                    },
                ]);
            })

            // Steps
            ->when(in_array('steps', $fields), function($query) {
                $query->with([
                    'steps' => function($query) {
                        $query->select('id', 'name', 'color', 'bgcolor');
                    },
                ]);
            })

            // Commissions
            ->when(in_array('commissions', $fields), function($query) {
                $query->with([
                    'actions' => function($query) {
                        $query->select('id', 'name', 'color', 'bgcolor');
                    },
                ]);
            })

            ->filter($orderRequestFilters)

            ->when($sortBy && $sortOrder, function($query) use($sortBy, $sortOrder) {
                $query->orderBy($sortBy, $sortOrder);
            })
            ->skip(($request->input('page', 1) - 1) * $count)
            ->paginate($count);
    }
    
    /**
     * Get fields
     */
    protected function getFields(Request $request, Project $project)
    {
        // From params
        if ($request->has('fields')) {
            return explode(',', $request->input('fields'));
        }
        
        // From user settings
        $userSetting = UserSetting::where([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'key' => 'orders-table',
        ])->first();

        if ($userSetting) {
            return array_map(function($field) {
                return $field['key'];
            }, $userSetting->value);
        }

        // From project settings
        $projectSetting = ProjectSetting::get($project, 'orders-table');

        if ($projectSetting) {
            return $projectSetting->map(function($field) {
                return $field['key'];
            })->toArray();
        }

        // Default
        return [
            "created_at", 
            "total_commissions", 
            "total",
            "prospect", 
            "products",
            "status", 
            "steps",
            "commissions"
        ];
    }
}
