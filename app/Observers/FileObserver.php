<?php

namespace App\Observers;

use App\Models\File;
use App\Notifications\GoogleDriveDeletedFile;
use App\Notifications\GoogleDriveNewFile;
use Illuminate\Support\Facades\Storage;

class FileObserver
{
    /**
     * Handle the File "creating" event.
     */
    public function created(File $file): void
    {
        // Creator
        $this->storeGoogleDrive($file);
    }
    
    /**
     * Handle the File "creating" event.
     */
    public function deleting(File $file): void
    {
        $this->deleteFile($file);
        $this->removeGoogleDrive($file);
    }
    
    /**
     * Delete file
     *
     * @param  \App\Models\File  $file
     * @return void
     */
    protected function deleteFile(File $file)
    {
        if (!Storage::disk('folders')->exists($file->path)) {
            return;
        }

        Storage::disk('folders')->delete($file->path);
    }
    
    /**
     * Store file to google drive
     *
     * @param  \App\Models\File  $file
     * @return void
     */
    protected function storeGoogleDrive(File $file)
    {
        if ($file->source != 'google-drive') {
            return;
        }

        // To do
        $file->folder->project->notify(new GoogleDriveNewFile($file));
    }
    
    /**
     * Delete file from google drive
     *
     * @param  \App\Models\File  $file
     * @return void
     */
    protected function removeGoogleDrive(File $file)
    {
        if ($file->source != 'google-drive') {
            return;
        }

        // To do
        $file->folder->project->notify(new GoogleDriveDeletedFile($file));
    }
}
