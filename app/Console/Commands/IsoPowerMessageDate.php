<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class IsoPowerMessageDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:iso-power-message-date';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ISO POWER messages date';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $messages = DB::table('messages')
            ->join('prospects', 'prospects.id', '=', 'messages.prospect_id')
            ->where('prospects.project_id', 14)
            ->where('messages.thread_id', 169)
            ->select('messages.id', 'messages.body', 'prospects.created_at')
            ->get();

        foreach ($messages as $message) {
            if (preg_match('/(\\d{2})\\/(\\d{2})\\/(\\d{4})/', $message->body, $matches, PREG_OFFSET_CAPTURE)) {
                $createdAt = $matches[3][0] . '-' . $matches[2][0] . '-' . $matches[1][0];
            } else if (preg_match('/(\\d{2})\\/(\\d{2})\\/(\\d{2})/', $message->body, $matches, PREG_OFFSET_CAPTURE)) {
                $createdAt = '20' . $matches[3][0] . '-' . $matches[2][0] . '-' . $matches[1][0];
            } else if (preg_match('/(\\d{2})\\/(\\d{2})/', $message->body, $matches, PREG_OFFSET_CAPTURE)) {
                if ($matches[2][0] >= 22) {
                    $createdAt = '20' . $matches[2][0] . '-' . $matches[1][0] . '-01';
                } else if ($matches[2][0] <= 12) {
                    $createdAt = substr($message->created_at, 0, 4) . '-' . $matches[2][0] . '-' . $matches[1][0];
                } else {
                    continue;
                }
            } else {
                continue;
            }

            $createdAt .= ' 00:00:00';

            $query = "UPDATE messages SET created_at = '{$createdAt}' WHERE id = '{$message->id}' LIMIT 1";
            DB::update($query);
        }

        return self::SUCCESS;
    }

}
