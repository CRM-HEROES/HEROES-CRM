<?php

namespace App\Console\Commands\Campaign\Prospect;

use App\Console\Commands\Campaign\Action;
use App\Mail\CampaignMail;
use App\Models\Prospect;
use App\Utils\Field\Renderer\ProjectFieldRenderer;
use App\Utils\Field\Renderer\ProspectFieldRenderer;
use Illuminate\Support\Facades\Mail;

/**
 * Send an email to the prospect
 *
 * Data format:
 * {
 *    subject: {subject of the email},
 *    body: {HTML body of the email},
 * }
 */
class ProspectEmailAction extends Action
{
    /**
     *
     */
    public function handle()
    {
        if (!$this->model instanceof Prospect) {
            return;
        }

        if (!$this->action->value) {
            return;
        }

        if (!isset($this->action->value['subject']) || !$this->action->value['subject']) {
            return;
        }

        if (!isset($this->action->value['body']) || !$this->action->value['body']) {
            return;
        }

        if (!$this->model->email) {
            return;
        }

        $renderers = [
            new ProspectFieldRenderer($this->model->project, $this->model),
            new ProjectFieldRenderer($this->model->project),
        ];

        $subject = array_reduce($renderers, function($carry, $renderer) {
            return $renderer->render($carry);
        }, $this->action->value['subject']);

        $body = array_reduce($renderers, function($carry, $renderer) {
            return $renderer->render($carry);
        }, $this->action->value['body']);

        Mail::to($this->model->email)->queue(new CampaignMail($subject, $body));
    }
}
