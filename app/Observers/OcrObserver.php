<?php

namespace App\Observers;

use App\Jobs\ProspectImportOCR;
use App\Jobs\ProspectOCR;
use App\Jobs\ProspectSendToThreadMessage;
use App\Jobs\ProspectSendToUserThreadMessage;
use App\Models\Message;
use \App\Jobs\ProspectThreadMessage;
use App\Models\Ocr;
use App\Utils\Field\Renderer\ProjectFieldRenderer;
use App\Utils\Field\Renderer\ProspectFieldRenderer;
use Illuminate\Support\Facades\Storage;

class OcrObserver
{
    /**
     * Handle the Message "creating" event.
     */
    public function created(Ocr $ocr): void
    {
        $this->scan($ocr);
    }
   
    /**
     * 
     */
    protected function scan(Ocr $ocr)
    {
        if ($ocr->api == 'mindee') {
            ProspectOCR::dispatchAfterResponse($ocr);
        } else if ($ocr->api == 'import') {
            ProspectImportOCR::dispatch($ocr)->onQueue('imports');
        }
    }
}
