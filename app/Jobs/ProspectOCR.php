<?php

namespace App\Jobs;

use App\Models\Ocr;
use App\Models\Project;
use App\Services\Mindee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProspectOCR implements ShouldQueue
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
    public function handle(Mindee $mindee)
    {
        $mapping = $this->getMapping();
        if (!$mapping) {
            return;
        }

        $prediction = $this->getPrediction($mindee);
        if (!$prediction) {
            return;
        }
        
        $data = $this->predictionToProspect($prediction, $mapping);
        $prospect = $this->ocr->project->prospects()->create($data);
        $this->ocr->update([
            'prediction' => $prediction,
            'prospect_id' => $prospect->id
        ]);
    }
    
    /**
     * 
     */
    protected function getPrediction(Mindee $mindee)
    {
        $disk = Storage::disk("ocr");
        $result = $mindee->predict(
            $this->ocr->type, 
            $disk->path($this->ocr->path), 
            $disk->mimeType($this->ocr->path)
        );
        
        if (
            !$result || 
            !isset($result['document']) || 
            !isset($result['document']['inference']) || 
            !isset($result['document']['inference']['prediction'])
        ) {
            return null;
        }

        return $result['document']['inference']['prediction'];
    }

    /**
     * 
     */
    protected function getMapping()
    {
        if ($this->ocr->type == 'idcard_fr') {
            return [
                'first_name' => 'given_names',
                'last_name' => 'surname',
                /*'birth_date' => 'birth_date',
                'birth_place' => 'birth_place',
                'gender' => 'gender',
                'card_access_number' => 'card_access_number',*/
            ];
        } else if ($this->ocr->type == 'visit_card') {
            return [
                'street' => 'address',
                'email' => 'email',
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'mobile_phone_number' => 'phone_number',
                'website_url' => 'website',
            ];
        } else if ($this->ocr->type == 'carte_de_visite') {
            return [
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'mobile_phone_number' => 'mobile_phone_number',
                'phone_number' => 'phone_number',
                'website_url' => 'web_site',
                'street' => 'street',
                'postal_code' => 'postal_code',
                'city' => 'city',
                'country' => 'country',
                'job_title' => 'job',
                'company_name' => 'company',
            ];
        }

        return null;
    }

    /**
     * 
     */
    protected function predictionToProspect(&$prediction, &$mapping)
    {
        $data = [];

        foreach ($mapping as $field => $predictionField) {
            if (isset($prediction[$predictionField])) {
                $pred = $prediction[$predictionField];

                if (array_is_list($pred)) {
                    $value = implode(' ', array_filter(array_map(function($v) {
                        if (isset($v["value"])) {
                            return $v["value"];
                        } else if (isset($v["values"])) {
                            usort($v["values"], function($a, $b) {
                                $x1 = $a['polygon'][0][0]; // X-coordinate of the first point of $a
                                $x2 = $b['polygon'][0][0]; // X-coordinate of the first point of $b
                                
                                // Compare the X-coordinates to determine the sorting order
                                if ($x1 < $x2) {
                                    return -1;
                                } elseif ($x1 > $x2) {
                                    return 1;
                                } else {
                                    return 0;
                                }
                            });
    
                            return implode(' ', array_filter(array_map(function($v2) {
                                return $v2["content"];
                            }, $v["values"])));
                        } else {
                            return null;
                        }
                    }, $pred)));
                } else {
                    if (isset($pred["value"])) {
                        $value = $pred["value"];
                    } else if (isset($pred["values"])) {
                        usort($pred["values"], function($a, $b) {
                            $x1 = $a['polygon'][0][0]; // X-coordinate of the first point of $a
                            $x2 = $b['polygon'][0][0]; // X-coordinate of the first point of $b
                            
                            // Compare the X-coordinates to determine the sorting order
                            if ($x1 < $x2) {
                                return -1;
                            } elseif ($x1 > $x2) {
                                return 1;
                            } else {
                                return 0;
                            }
                        });

                        $value = implode(' ', array_filter(array_map(function($v2) {
                            return $v2["content"];
                        }, $pred["values"])));
                    } else {
                        $value = null;
                    }
                }
    
                $data[$field] = $value;
            }
        }

        return $data;
    }
}
