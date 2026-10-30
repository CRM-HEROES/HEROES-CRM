<?php

namespace Tests\Support;

use App\Models\WorkflowExecution;
use App\Workflows\Actions\ActionHandlerInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Workflow action for the tests: records its calls ("name:subjectId").
 */
class RecordingHandler implements ActionHandlerInterface
{
    public static array $calls = [];

    public function __construct(protected string $name, protected bool $fail = false)
    {
    }

    public function handle(Model $subject, array $config, WorkflowExecution $execution): array
    {
        self::$calls[] = $this->name . ':' . $subject->id;

        if ($this->fail) {
            throw new \RuntimeException('The provider is down');
        }

        return ['done' => true];
    }
}
