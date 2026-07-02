<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Label;
use App\Models\Ocr;
use App\Models\Prospect;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProspectImportOCR implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Ocr $ocr;

    /**
     * Create a new job instance.
     */
    public function __construct(Ocr $ocr)
    {
        $this->ocr = $ocr;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $import = $this->ocr->project->imports()->where('id', $this->ocr->type)->first();

        if (!$import) {
            return;
        }

        $disk = Storage::disk("ocr");
        $path = $disk->path($this->ocr->path);
        
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($path);

        $text = $pdf->getText();

        // 20/02/2024 17h00
        preg_match('/(\\d{2})\\/(\\d{2})\\/(\\d{4}) (\\d{2})h(\\d{2})/', $text, $matchEvent);

        $text = explode("\n", $text);
        $text = array_map(function($t) {
            return explode("\t", $t);
        }, $text);
        $text = array_filter($text, function($t) {
            return count($t) == 2;
        });
        $values = [];

        foreach ($text as $t) {
           $values[trim($t[0])] = trim($t[1]); 
        }

        $prospect = $this->createProspect($import, $values);

        $this->createEvent($prospect, $matchEvent);

        $disk->delete($this->ocr->path);
    }
    
    /**
     * 
     * Create prospect from data parsed from pdf file
     */
    protected function createProspect($import, $values)
    {
        // Check that there is a field mapping 
        // between the database 
        // and the information sent from the external server
        if (!$import->headers || !$import->mapping) {
            return response('KO/L\'import via le webservice n\'a pas été encore bien configuré.', 400);
        }

        $prospect = new Prospect([
            'import_id' => $import->id,
            'creator_id' => $import->creator_id,
            'project_id' => $import->project_id
        ]);

        $meta = [];
        $labels = [];

        // update prospect field 
        // according to the import mapping
        foreach ($import->mapping as $index => $attribute) {

            if (is_null($attribute)) continue;

            $param = trim($import->headers[$index]);
            if (array_key_exists($param, $values)) {
                $value = $values[$param];

                // update meta field
                if (Str::startsWith($attribute, 'meta->')) {
                    $meta[str_replace('meta->', '', $attribute)] = $value;
                // update classic field
                } else if (Str::startsWith($attribute, 'category->')) {
                    $category = Category::find(str_replace('category->', '', $attribute));

                    if ($category) {
                        if (!empty($value)) {
                            $label = $category->labels()->where('name', $value)->first();

                            // if label is not found inside this category
                            // we create a none validated label
                            if (!$label) {
                                $label = new Label([
                                    'name' => $value,
                                    'color' => "#ffffff",
                                    'bgcolor' => "#000000",
                                    'validated' => 0
                                ]);

                                $category->labels()->save($label);
                            }

                            $labels[] = $label->id;
                        }
                    }
                } else {
                    $prospect[$attribute] = $value;
                }
            }
        }

        $prospect->meta = $meta;
        $prospect->save();

        if ($import->users) {
            $prospect->users()->attach($import->users);
        }

        if ($import->groups) {
            $prospect->groups()->attach($import->groups);
        }

        if ($import->labels) {
            $prospect->labels()->attach($import->labels);
        }

        $prospect->labels()->syncWithoutDetaching($labels);
        
        $this->ocr->update([
            'prospect_id' => $prospect->id
        ]);

        return $prospect;
    }
    
    /**
     * 
     */
    protected function createEvent($prospect, $matchEvent)
    {
        if (count($matchEvent) < 6) {
            return;
        }

        $calendar = $prospect->project->calendars()->where('type', 'physical')->first();

        if (!$calendar) {
            return;
        }

        $user = $prospect->project->users()->where('email', 'sionstevens@gmail.com')->first();

        if (!$user) {
            return;
        }

        $start = \Carbon\Carbon::create($matchEvent[3], $matchEvent[2], $matchEvent[1], $matchEvent[4], $matchEvent[5]);
        $end = $start->copy();
        $end->addHour();

        $prospect->events()->create([
            'name' => $prospect->full_name,
            'description' => "Fiche prospect <a>" . $prospect->full_name ."</a>",
            'calendar_id' => $calendar->id,
            'user_id' => $user->id,
            'started_at' => $start->format("Y-m-d H:i:s"),
            'ended_at' => $end->format("Y-m-d H:i:s"),
            'location' => $prospect->address
        ]);
    }
}
