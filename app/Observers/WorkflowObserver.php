<?php

namespace App\Observers;

use App\Models\Event;
use App\Models\Order;
use App\Models\Prospect;
use App\Workflows\WorkflowEvents;
use App\Workflows\WorkflowSubject;
use Illuminate\Database\Eloquent\Model;

/**
 * Reports the creation and the update of prospects, orders and appointments
 * to the workflow engine.
 *
 * A separate observer, registered after the existing ones: it runs when they
 * are done, so the workflows see the final state (assignments, default
 * values...), and the existing observers are left untouched.
 */
class WorkflowObserver
{
    public function created(Model $model): void
    {
        WorkflowEvents::record($this->subjectType($model), $model, 'created');
    }

    public function updated(Model $model): void
    {
        WorkflowEvents::record($this->subjectType($model), $model, 'updated');
    }

    protected function subjectType(Model $model): string
    {
        return match (true) {
            $model instanceof Prospect => WorkflowSubject::PROSPECT,
            $model instanceof Order => WorkflowSubject::ORDER,
            $model instanceof Event => WorkflowSubject::EVENT,
        };
    }
}
