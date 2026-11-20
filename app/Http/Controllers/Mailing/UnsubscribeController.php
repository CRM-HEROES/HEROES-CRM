<?php

namespace App\Http\Controllers\Mailing;

use App\Http\Controllers\Controller;
use App\Workflows\Mailing\MailingRules;
use Illuminate\Support\Facades\DB;

/**
 * The link "Ne plus recevoir ces emails" of the emails of the automations.
 *
 * Public (the person is not logged in) and signed: the address is made by the
 * CRM for one prospect (the variable prospect.unsubscribe_url) and cannot be
 * made for another. It marks the prospect with the custom field the sending
 * rules read (mailing_optout), without going through the model: this is not a
 * change that other automations or integrations have to hear of.
 */
class UnsubscribeController extends Controller
{
    public function __invoke(int $prospect)
    {
        $row = DB::table('prospects')->where('id', $prospect)->whereNull('deleted_at')->first(['id', 'project_id', 'meta']);

        abort_unless($row, 404);

        $slug = MailingRules::DEFAULTS['optout_field'];
        $meta = is_string($row->meta) ? (json_decode($row->meta, true) ?: []) : (array) $row->meta;

        if (empty($meta[$slug])) {
            $meta[$slug] = now()->toDateString();

            DB::table('prospects')->where('id', $row->id)->update(['meta' => json_encode($meta, JSON_UNESCAPED_UNICODE)]);
        }

        $project = DB::table('projects')->where('id', $row->project_id)->value('name');

        return response()->view('mailing.unsubscribed', ['project' => $project]);
    }
}
