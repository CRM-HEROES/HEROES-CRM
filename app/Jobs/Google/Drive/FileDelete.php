<?php

namespace App\Jobs\Google\Drive;

use App\Models\File;
use App\Models\GoogleAccount;
use App\Services\Google\Drive as GoogleDrive;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FileDelete implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected GoogleAccount $googleAccount;
    protected File $file;

    /**
     * Create a new job instance.
     */
    public function __construct(GoogleAccount $googleAccount, File $file)
    {
        $this->googleAccount = $googleAccount;
        $this->file = $file;
    }

    /**
     * Execute the job.
     */
    public function handle(GoogleDrive $googleDrive): void
    {
        $googleDriveFile = $this->googleAccount->files()->withPivot('google_file_id')->find($this->file->id);

        if (!$googleDriveFile) {
            return;
        }

        try {
            $googleDrive->setAccessToken($this->googleAccount->token);
    
            if ($googleDrive->isAccessTokenExpired()) {              
                $googleDrive->fetchAccessTokenWithRefreshToken($googleDrive->getRefreshToken());
                $token = $googleDrive->getAccessToken();
                $googleDrive->setAccessToken($token);    
                $this->googleAccount->update(['token' => $token]);
            }

			$service = new \Google_Service_Drive($googleDrive);
            $service->files->delete($googleDriveFile->pivot->google_file_id);
            $this->googleAccount->files()->detach($this->file->id);    
        } catch(\Exception $e) {
            throw $e;
        }
    }
}
