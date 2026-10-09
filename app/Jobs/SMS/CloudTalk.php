<?php

namespace App\Jobs\SMS;

use App\Models\Line;
use App\Models\Sms;
use App\Services\CloudTalk as CloudTalkService;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CloudTalk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Sms $sms;

    /**
     * Create a new job instance.
     */
    public function __construct(Sms $sms)
    {
        $this->sms = $sms;
    }

    /**
     * Execute the job.
     */
    public function handle(CloudTalkService $cloudTalk): void
    {
        try {
            $line = $this->cloudTalkLine();

            if (!$line) {
                throw new Exception('Aucune ligne CloudTalk SMS affectee a cet agent.');
            }

            $cloudTalk->sendSms(
                $line,
                (string) $this->sms->prospect->mobile_phone_number,
                (string) $this->sms->message
            );

            $this->sms->update(['sent_at' => Carbon::now()]);
        } catch (Exception $e) {
            $this->sms->update(['error' => $e->getMessage()]);
            \ProjectLog::error($this->sms->prospect->project, "CloudTalk: " . $e->getMessage());
        }
    }

    protected function cloudTalkLine(): ?Line
    {
        return $this->sms
            ->prospect
            ->project
            ->lines()
            ->where('operator', 'cloudtalk')
            ->where('user_id', $this->sms->creator_id)
            ->get()
            ->first(fn (Line $line) => !empty($line->numero));
    }
}
