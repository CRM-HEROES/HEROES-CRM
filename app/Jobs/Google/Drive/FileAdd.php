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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FileAdd implements ShouldQueue
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
        try {
            $googleDrive->setAccessToken($this->googleAccount->token);
    
            if ($googleDrive->isAccessTokenExpired()) {              
                $googleDrive->fetchAccessTokenWithRefreshToken($googleDrive->getRefreshToken());
                $token = $googleDrive->getAccessToken();
                $googleDrive->setAccessToken($token);    
                $this->googleAccount->update(['token' => $token]);
            }

			$service = new \Google_Service_Drive($googleDrive);

            $folder = $this->file->folder;
            $prospect = $this->file->prospect;
            $project = $folder->project;

            // Google drive project folder
            $googleDriveProjectFolder  = $this->getOrCreateFolder($service, null, $project->name, $project);
            // Google drive prospect folder
            $googleDriveProspectFolder = $this->getOrCreateFolder($service, $googleDriveProjectFolder->google_folder_id, $prospect->full_name, $project, $prospect);
            // Google drive folder folder
            $googleDriveFolderFolder   = $this->getOrCreateFolder($service, $googleDriveProspectFolder->google_folder_id, $folder->name, $project, $prospect, $folder);

            // Store File in Google drive folder folder
            $googleDriveFolderFile = $this->createGoogleFile($service, $googleDriveFolderFolder->google_folder_id, $this->file);
            $this->googleAccount->files()->syncWithoutDetaching([$this->file->id => [
                'google_file_id'   => $googleDriveFolderFile->getId(),
                'icon_link'        => $googleDriveFolderFile->getIconLink(),
                'thumbnail_link'   => $googleDriveFolderFile->getThumbnailLink(),
                'web_content_link' => $googleDriveFolderFile->getWebContentLink(),
                'web_view_link'    => $googleDriveFolderFile->getWebViewLink(),
            ]]);
        } catch(\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * 
     * Create new folder into Google Drive
     * 
     * @param $service
     * 
     * @param google calendar ID
     * 
     */
    private function createGoogleFolder($service, $folderParentId, $folderName)
    {
        $googleDriveFolder = new \Google_Service_Drive_DriveFile();
        $googleDriveFolder->setName($folderName);
        $googleDriveFolder->setMimeType('application/vnd.google-apps.folder');

        if ($folderParentId) {
            $googleDriveFolder->setParents([$folderParentId]);
        }

        return $service->files->create($googleDriveFolder);
    }

    /**
     * 
     * Create new folder into Google Drive
     * 
     * @param $service
     * 
     * @param google calendar ID
     * 
     */
    private function getOrCreateFolder($service, $folderParentId, $folderName, $project, $prospect = null, $folder = null)
    {
        // Google drive folder

        $params = [
            'google_account_id' => $this->googleAccount->id,
            'project_id' => $project->id,
            'prospect_id' => $prospect ? $prospect->id : null,
            'folder_id' => $folder ? $folder->id : null,
        ];

        $googleDriveFolder = DB::table('google_folder')->where($params)->first();

        // Check if folder has not been created

        if (!$googleDriveFolder) {
            $result = $this->createGoogleFolder($service, $folderParentId, $folderName);

            DB::table('google_folder')->updateOrInsert($params, [
                'google_folder_id' => $result->getId()
            ]);
                
            $googleDriveFolder = DB::table('google_folder')->where($params)->first();
        }

        // Check if folder still exists in Google Drive
        // To do ...

        return $googleDriveFolder;
    }

    /**
     * 
     * Create new folder into Google Drive
     * 
     * @param $service
     * 
     * @param google calendar ID
     * 
     */
    private function createGoogleFile($service, $folderParentId, File $file)
    {
        $googleDriveFile = new \Google_Service_Drive_DriveFile();
        $googleDriveFile->setName($file->name);
        $googleDriveFile->setParents([$folderParentId]);

        $disk = Storage::disk('folders');

        return $service->files->create($googleDriveFile, array(
            'data' => file_get_contents($disk->path($file->path)),
            'mimeType' => $disk->mimeType($file->path),
            'fields' => 'id'
        ));
    }

    /**
     * 
     * Share Google folder
     * 
     * @param $service
     * 
     * @param google calendar ID
     * 
     */
    private function shareFolder($service, $folderId, $email, $role = 'writer')
    {
        try {
            $userPermission = new \Google_Service_Drive_Permission([
              'type' => 'user',
              'role' => $role,
              'emailAddress' => $email
            ]);

            $service->permissions->create(
                $folderId, 
                $userPermission, 
                ['fields' => 'id']
            );

            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

}
