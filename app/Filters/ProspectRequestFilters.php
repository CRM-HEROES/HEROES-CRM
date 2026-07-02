<?php

namespace App\Filters;

use Illuminate\Http\Request;

class ProspectRequestFilters extends ProspectFilters
{
    use TraitRequestFilters;

    protected $params = [
        'ids',
        'createdAt',
        'createdAfter',
        'createdBefore',
        'createdDaysAgo',
        'createdBeforeDays',
        'imports',
        'polygon',
        'position',
        'processed',
        'processedBy',
        'processedAfter',
        'processedBefore',
        'query',
        'scopeUser',
        'trashed',
        'unknownAddress',
        'validAddress',
        'withCreators',
        'withoutCreators',
        'withGroups',
        'withoutGroups',
        'withImports',
        'withoutImports',
        'withFiles',
        'withoutFiles',
        'withMessages',
        'withoutMessages',
        'withInteractions',
        'withoutInteractions',
        'withPipedriveAccounts',
        'withoutPipedriveAccounts',
        'withPipelines',
        'withoutPipelines',
        'withSms',
        'withoutSms',
        'withEvents',
        'withoutEvents',
        'withOrders',
        'withoutOrders',
        'withUsers',
        'withoutUsers',
        'withThreads',
        'withoutThreads',
        'waitingUsers',
        'archivedWaitingUsers',
    ];

    /**
     * Create a new Filters instance
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        if ($request->project) {
            // Fields
            foreach ($request->project->fields as $field) {
                $this->params[] = ($field->meta ? 'meta_' : 'field_') . $field->slug;
            }
            // With fields
            foreach ($request->project->fields as $field) {
                $this->params[] = ($field->meta ? 'withMeta_' : 'withField_') . $field->slug;
            }
            // Labels
            foreach ($request->project->categories as $category) {
                $this->params[] = 'withCategory_' . $category->id;
                $this->params[] = 'withoutCategory_' . $category->id;
            }
            // Threads
            foreach ($request->project->threads as $thread) {
                $this->params[] = 'thread_' . $thread->id;
            }
        }

        $this->addRequestFilters($this->params);
        // $this->addMenuFilters($request);
    }

    /**
     * Add filter from
     * the given menu
     * from request
     */
    protected function addMenuFilters(Request $request) {
        if (!$request->has('menu')) {
            return;
        }

        $menu = $request->input('menu');

        // Default menu: all, trash, processed, ...
        if ($this->addDefaultMenuFilters($menu)) {
            return;
        }

        $menu = $request->project->menus()->find($menu);
        if (!$menu) {
            return;
        }

        // Custom menu
        $this->addMenuFilter($menu);
    }
}
