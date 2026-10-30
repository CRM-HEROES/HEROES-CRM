<?php

namespace App\Workflows\Nodes;

use App\Models\WorkflowExecution;
use App\Workflows\WorkflowSubject;
use Illuminate\Database\Eloquent\Model;

/**
 * What a node executor knows: the execution, its own node and (lazily,
 * once) the subject the workflow runs on.
 */
class NodeContext
{
    protected bool $subjectLoaded = false;
    protected ?Model $subject = null;

    public function __construct(
        public WorkflowExecution $execution,
        public array $node,
    ) {
    }

    /**
     * Configuration of the node
     */
    public function config(): array
    {
        return $this->node['config'] ?? [];
    }

    /**
     * The subject (null if it was deleted or is in another project)
     */
    public function subject(): ?Model
    {
        if (!$this->subjectLoaded) {
            $this->subject = WorkflowSubject::find(
                $this->execution->subject_type,
                $this->execution->subject_id,
                $this->execution->project_id
            );
            $this->subjectLoaded = true;
        }

        return $this->subject;
    }
}
