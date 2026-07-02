<?php

namespace App\Console\Commands\Stats;

use App\Console\Commands\Stats\StatInterface;
use App\Models\Folder;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ProjectNewFilesStat implements StatInterface
{
    protected Project $project;
    protected ?Folder $folder;
    protected $country;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Project $project, ?Folder $folder = null, $country = null)
    {
        $this->project = $project;
        $this->folder = $folder;
        $this->country = $country;
    }

    /**
     * 
     */
    public function key()
    {
        return "prospect.file.new" . ($this->folder ? ".folder." . $this->folder->id : "") . ($this->country ? ".country." . $this->country : "");
    }
    
    /**
     * 
     */
    public function value($date, $userId = null)
    {
        return DB::table('files')
            ->join('prospects', 'prospects.id', '=', 'files.prospect_id')
            ->where('prospects.project_id', $this->project->id)
            ->whereBetween('files.created_at', [$date . ' 00:00:00', $date . ' 23:59:59'])
            ->when($this->folder, function($query) {
                $query->where('files.folder_id', $this->folder->id);
            })
            ->when($this->country, function($query) {
                $query->where('prospects.country', $this->country);
            })
            ->when($userId, function($query) use ($userId) {
                $query->where('files.creator_id', $userId);
            })
            ->count();
    }
}
