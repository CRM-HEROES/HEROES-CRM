<?php

namespace App\Http\Controllers\Webhook\Project;

use App\Http\Controllers\Controller;
use App\Jobs\Pipedrive\PersonShow;
use App\Jobs\Pipedrive\StageShow;
use App\Models\Label;
use App\Models\PipedriveAccount;
use App\Models\Project;
use App\Models\Prospect;
use App\Services\Pipedrive;
use App\Utils\ProjectSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Webhook for Pipedrive
 */
class PipedriveController extends Controller
{

    /**
     * 
     */
    protected function authenticate(Request $request, Project $project)
    {
        // User and password required
        abort_unless($request->hasHeader('php-auth-user'), 401, 'Pipedrive webhook authentication failed');
        abort_unless($request->hasHeader('php-auth-pw'), 401, 'Pipedrive webhook authentication failed');
        // Get associated project
        abort_unless($p = Project::where('slug', $request->header('php-auth-user'))->first(), 401, 'Pipedrive webhook authentication failed');
        abort_unless($p->id == $project->id, 401, 'Pipedrive webhook authentication failed');
        
        // Get associated pipedrive account
        abort_unless($pipedriveAccount = PipedriveAccount::where('project_id', $project->id)->where('password', $request->header('php-auth-pw'))->first(), 401, 'Pipedrive webhook authentication failed');

        return $pipedriveAccount;
    }
    
    /**
     * 
     */
    public function personStore(Request $request, Project $project)
    {
        $pipedriveAccount = $this->authenticate($request, $project);

        // Data
        $data = $request->post();

        // Check event action
        abort_unless(isset($data['event']) && $data['event'] == 'added.person', 400);

        // Check added person
        abort_unless(isset($data['current']), 400);

        $person = $data['current'];
        $previous = $data['previous'];
        
        // Find mapped prospect
        $prospect = DB::table('pipedrive_person')
            ->where('pipedrive_account_id', $pipedriveAccount->id)
            ->where('person_id', $person['id'])
            ->first();
        
        abort_unless(!$prospect, 404);

        // Store the added prospect
        $fields = ProjectSetting::get($project, 'pipedrive.fields', []);

        $mappedProspect = $this->mapPersonToProspect($person, $previous, $fields);
        
        $prospect = DB::table('prospects')
            ->insertGetId(
                array_merge(
                    $mappedProspect,
                    [
                        'meta' => json_encode($mappedProspect['meta']),
                        'project_id' => $project->id
                    ]
                )
            );
            
        // Map the added person
        // with newly created prospect
        DB::table('pipedrive_person')->insert([
            'pipedrive_account_id' => $pipedriveAccount->id,
            'prospect_id' => $prospect,
            'person_id' => $person['id'],
            'from_pipedrive' => 1,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now()
        ]);

        return ['message' => trans('common.success.updated_resource')];
    }
    
    /**
     * 
     */
    public function personUpdate(Request $request, Project $project)
    {
        $pipedriveAccount = $this->authenticate($request, $project);

        // Data
        $data = $request->post();
        
        // Check event action
        abort_unless(isset($data['event']) && $data['event'] == 'updated.person', 400);

        // Check updated person
        abort_unless(isset($data['current']), 400);
        $person = $data['current'];
        $previous = $data['previous'];

        // Find mapped prospect
        $prospect = DB::table('pipedrive_person')
            ->where('pipedrive_account_id', $pipedriveAccount->id)
            ->where('person_id', $person['id'])
            ->first(['prospect_id']);

        abort_unless($prospect, 404);

        $prospect = Prospect::withTrashed()->where('id', $prospect->prospect_id)->first();

        // Update prospect
        $fields = ProjectSetting::get($project, 'pipedrive.fields', []);
        
        $mappedProspect = $this->mapPersonToProspect($person, $previous, $fields);

        DB::table('prospects')
            ->where('id', $prospect->id)
            ->limit(1)
            ->update(
                array_merge(
                    $mappedProspect, [
                        'meta' => json_encode(array_merge(
                            $prospect->meta ?: [], 
                            $mappedProspect['meta']
                        ))
                    ]
                )
            );

        return ['message' => trans('common.success.updated_resource')];
    }
    
    /**
     * 
     */
    public function dealStore(Request $request, Project $project, Pipedrive $pipedrive)
    {
        $pipedriveAccount = $this->authenticate($request, $project);

        // Data
        $data = $request->post();
        
        // Check event action
        abort_unless(isset($data['event']) && $data['event'] == 'added.deal', 400);

        // Check updated deal
        abort_unless(isset($data['current']), 400);
        $deal = $data['current'];

        // ISO POWER,
        // store only won deal
        if (!isset($deal['status']) || $deal['status'] != "won") {
            return;
        }

        // Find mapped prospect
        $prospect = $this->personMappedProspect($pipedriveAccount, $deal['person_id']);
        if (!$prospect) {
            PersonShow::dispatch($pipedriveAccount, $deal['person_id'])->onConnection('sync');
            $prospect = $this->personMappedProspect($pipedriveAccount, $deal['person_id']);
        }

        // Update prospect
        $fields = ProjectSetting::get($project, 'pipedrive.deal.fields', []);
        $prospect = Prospect::withTrashed()->where('id', $prospect->prospect_id)->first();
        $payload = $this->mapDeal($deal, $prospect, $fields);
        
        // Update prospect labels
        $this->associateProspectLabels($prospect, $payload['labels']); // Mapped labels
        $this->associateProspectDefaultLabels($prospect); // Default labels
        unset($payload['labels']);
        
        DB::table('prospects')
            ->where('id', $prospect->id)
            ->limit(1)
            ->update($payload);

        // Won status
        if (isset($deal['status']) && $deal['status'] == "won") {
            $projectSetting = ProjectSetting::get($project, 'pipedrive.deal.status.won');
            
            // 1448
            if ($projectSetting) {
                $prospect->labels()->syncWithoutDetaching(intval($projectSetting));
            }
        }
        
        // Lost status
        if (isset($deal['status']) && $deal['status'] == "lost") {
            $projectSetting = ProjectSetting::get($project, 'pipedrive.deal.status.lost');
            
            if ($projectSetting) {
                $prospect->labels()->syncWithoutDetaching(intval($projectSetting));
            }
        }

        // Pipedrive label
        if (isset($deal['stage_id']) && $deal['stage_id']) {
            // Get stage name
            // StageShow::dispatch($pipedriveAccount, $deal['pipeline_id'], $deal['stage_id']);
            // Get associated label to the stage
            $label = $this->getMappedLabel($project, $deal['pipeline_id'], $deal['stage_id']);

            if ($label) {
                // Detach all labels related to the category of the stage label
                $prospect->labels()->detach(Label::where('category_id', $label->category_id)->get(['id'])->pluck('id')->toArray());
                // Attach the label to the prospect
                $prospect->labels()->syncWithoutDetaching($label->id);
            }
        }

        $this->getDealProducts($pipedriveAccount, $project, $prospect, $deal['id'], $pipedrive);
        $this->getDealNotes($pipedriveAccount, $project, $prospect, $deal['id'], $pipedrive);

        return ['message' => trans('common.success.updated_resource')];
    }
    
    /**
     * 
     */
    public function dealUpdate(Request $request, Project $project, Pipedrive $pipedrive)
    {
        $pipedriveAccount = $this->authenticate($request, $project);

        // Data
        $data = $request->post();
        
        // Check event action
        abort_unless(isset($data['event']) && $data['event'] == 'updated.deal', 400);

        // Check updated deal
        abort_unless(isset($data['current']), 400);
        $deal = $data['current'];
        $previous = $data['previous'];

        // ISO POWER,
        // store only won deal
        if (!isset($deal['status']) || $deal['status'] != "won") {
            return;
        }

        // Find or create mapped prospect
        $prospect = $this->personMappedProspect($pipedriveAccount, $deal['person_id']);
        if (!$prospect) {
            PersonShow::dispatch($pipedriveAccount, $deal['person_id'])->onConnection('sync');
            $prospect = $this->personMappedProspect($pipedriveAccount, $deal['person_id']);
        }

        // Update prospect
        $fields = ProjectSetting::get($project, 'pipedrive.deal.fields', []);
        $prospect = Prospect::withTrashed()->where('id', $prospect->prospect_id)->first();
        $payload = $this->mapDeal($deal, $prospect, $fields);

        // Update prospect labels
        $this->associateProspectLabels($prospect, $payload['labels']); // Mapped labels
        $this->associateProspectDefaultLabels($prospect); // Default labels
        unset($payload['labels']);
        
        DB::table('prospects')
            ->where('id', $prospect->id)
            ->limit(1)
            ->update($payload);

        // Won status
        if (isset($deal['status']) && $deal['status'] == "won") {
            $projectSetting = ProjectSetting::get($project, 'pipedrive.deal.status.won');
            
            // 1448
            if ($projectSetting) {
                $prospect->labels()->syncWithoutDetaching(intval($projectSetting));
            }
        }
        
        // Lost status
        if (isset($deal['status']) && $deal['status'] == "lost") {
            $projectSetting = ProjectSetting::get($project, 'pipedrive.deal.status.lost');
            
            if ($projectSetting) {
                $prospect->labels()->syncWithoutDetaching(intval($projectSetting));
            }
        }

        // Pipedrive label
        if ($deal['stage_id'] != $previous['stage_id']) {
            // Get stage name
            // StageShow::dispatch($pipedriveAccount, $deal['pipeline_id'], $deal['stage_id']);
            // Get associated label to the stage
            $label = $this->getMappedLabel($project, $deal['pipeline_id'], $deal['stage_id']);
            
            if ($label) {
                // Detach all labels related to the category of the stage label
                $prospect->labels()->detach(Label::where('category_id', $label->category_id)->get(['id'])->pluck('id')->toArray());
                // Attach the label to the prospect
                $prospect->labels()->syncWithoutDetaching($label->id);
            }
        }

        $this->getDealProducts($pipedriveAccount, $project, $prospect, $deal['id'], $pipedrive);
        $this->getDealNotes($pipedriveAccount, $project, $prospect, $deal['id'], $pipedrive);

        return ['message' => trans('common.success.updated_resource')];
    }
    
    /**
     * map person to prospect
     */
    public function mapPersonToProspect(&$person, &$previous, &$fields)
    {
        $prospect = [
            // Name
            'first_name'          => data_get($person, 'first_name', null),
            'last_name'           => data_get($person, 'last_name', null),
            'date_of_birth'       => data_get($person, 'birthday', null),

            // Contact
            'mobile_phone_number' => $this->getMobilePhoneNumber($person),
            'phone_number'        => $this->getPhoneNumber($person),
            'email'               => data_get($person, 'primary_email', null),
            
            // Address
            'latitude'            => data_get($person, 'postal_address_lat', null),
            'longitude'           => data_get($person, 'postal_address_long', null),
            'street'              => $this->getStreet($person),
            'postal_code'         => data_get($person, 'postal_address_postal_code', null),
            'country'             => data_get($person, 'postal_address_country', null),
            'state'               => data_get($person, 'postal_address_admin_area_level_1', null),
            'county'              => data_get($person, 'postal_address_admin_area_level_2', null),

            // Job
            'job_title'           => data_get($person, 'job_title', null),
            'company_name'        => data_get($person, 'org_name', null),
            
            // Timestamp
            'created_at'          => data_get($person, 'add_time', null),
            'updated_at'          => data_get($person, 'update_time', null),

            'meta'                => []
        ];
        
        foreach ($fields as $pipedriveField => $field) {
            if (!isset($person[$pipedriveField]) || !$person[$pipedriveField]) {
                continue;
            }
        
            if (Str::startsWith($field, 'meta->')) {
                $field = str_replace('meta->', '', $field);
                $prospect['meta'][$field] = $person[$pipedriveField];
            } else {
                $prospect[$field] = $person[$pipedriveField];
            }
        }

        return $prospect;
    }
    
    /**
     * Map deal from Pipedrive
     * to a heroes prospect
     */
    protected function mapDeal(&$deal, &$p, &$fields) {
        $prospect = [
            'meta' => $p->meta ?: [],
            'labels' => [],
        ];

        foreach ($fields as $pipedriveField => $field) {
            if (!isset($deal[$pipedriveField]) || !$deal[$pipedriveField]) {
                continue;
            }
        
            // Meta field
            if (Str::startsWith($field, 'meta->')) {
                $field = str_replace('meta->', '', $field);
                $prospect['meta'][$field] = $deal[$pipedriveField];
            // Category field
            } else if (Str::startsWith($field, 'category->')) {
                $categoryId = str_replace('category->', '', $field);
                $category = $p->project->categories()->where('id', $categoryId)->first();

                if (!$category) {
                    continue;
                }

                $label = $this->findOrCreateLabel($category, $deal[$pipedriveField]);
                $prospect['labels'][] = $label->id;
            // Default field
            } else {
                $prospect[$field] = $deal[$pipedriveField];
            }
        }

        $prospect['meta'] = json_encode($prospect['meta']);

        return $prospect;
    }

    /**
     * Default Prospect labels
     */
    protected function associateProspectLabels($prospect, $labels) {
        // Remove previous labels
        DB::table('prospect_label')
            ->where('prospect_id', $prospect->id)
            ->whereIn('label_id', $labels)
            ->limit(count($labels))
            ->delete();

        // Associate new labels
        DB::table('prospect_label')->insert(array_map(function($label) use ($prospect) {
            return [
                'prospect_id' => $prospect->id,
                'label_id' => $label,
                'created_at'  => \Carbon\Carbon::now(),
                'updated_at'  => \Carbon\Carbon::now(),
            ];
        }, $labels));
    }

    /**
     * Default Prospect associated labels
     */
    protected function associateProspectDefaultLabels($prospect) {
        // Default labels from settings
        $labels = (array) ProjectSetting::get($prospect->project, 'pipedrive.person.default.labels', []);
        $this->associateProspectLabels($prospect, $labels);
    }

    /**
     * Get mobile phone number
     * from a person
     */
    protected function getMobilePhoneNumber($person) {
        if (!isset($person['phone'])) {
            return null;
        }

        // Get number from this list
        // that is not labeled as "home"
        foreach ($person['phone'] as $phoneNumber) {
            if (!isset($phoneNumber['label']) || $phoneNumber['label'] != 'home') {
                return $phoneNumber['value'];
            }
        }

        return null;
    }

    /**
     * Get phone number
     * from a person
     */
    protected function getPhoneNumber($person) {
        if (!isset($person['phone'])) {
            return null;
        }

        // Get number from this list
        // that is labeled as "home"
        foreach ($person['phone'] as $phoneNumber) {
            if (isset($phoneNumber['label']) && $phoneNumber['label'] == 'home') {
                return $phoneNumber['value'];
            }
        }

        return null;
    }

    /**
     * Get street
     * from a person
     */
    protected function getStreet(&$person) {
        implode(
            ' ', 
            array_filter(array_map(function ($address) use($person) {
                return isset($person[$address]) ? $person[$address] : null;
            }, [
                'postal_address_street_number', 
                'postal_address_route', 
                'postal_address_sublocality', 
                'postal_address_locality'
            ]))
        );
    }

    /**
     * 
     */
    protected function personMappedProspect(PipedriveAccount $pipedriveAccount, $personId) {
        return DB::table('pipedrive_person')
            ->where('pipedrive_account_id', $pipedriveAccount->id)
            ->where('person_id', $personId)
            ->first(['prospect_id']);
    }
    
    /**
     * 
     */
    public function getDealProducts(PipedriveAccount $pipedriveAccount, Project $project, Prospect $prospect, $dealId, Pipedrive $pipedrive) {
        Config::set([
            'pipedrive.api_token' => $pipedriveAccount->token,
        ]); 

        // Get deal associated products
        // from pipedrive
        $response = $pipedrive->getDealProducts($dealId);
        if (!$response || !$response['success']) {
            return;
        }

        $products = is_array($response['data']) ? $response['data'] : [];
        // dd($products);
        // prospect orders
        $prospectOrders = $prospect->orders()->with('products')->get();
        // For eeach product,
        // find if the product is associated to the prospect
        foreach ($products as $product) {
            // Check if the current order
            // is associated to a product
            // with the same name
            // as the product from Pipedrive
            $findProspectProduct = $prospectOrders->filter(function($order) use($product) {
                return $order->products->filter(function($p) use($product) {
                    return $p->name == $product['name'];
                })->count() > 0;
            })->count() > 0;

            // else associate the product
            if (!$findProspectProduct) {
                $productParam = [
                    'price' => $product['item_price'],
                    'incuding_tax' => $product['tax_method'] == 'inclusive',
                    // 'tax' => $product['tax']
                ];

                // if the product does not already exists
                // in the prospect project
                // create it
                $project = $prospect->project;
                if (!($p = $project->products()->where('name', $product['name'])->first())) {
                    $p = $project->products()->create(array_merge([
                        'name' => $product['name'],
                    ], $productParam));
                }

                // Create new order 
                // and attach the product to the order
                $order = $prospect->orders()->create(['name' => $product['name']]);
                $order
                    ->products()
                    ->syncWithoutDetaching([$p->id => array_merge([
                        'quantity' => $product['quantity']
                    ], $productParam)]);
            }
        }
    }
    
    /**
     * 
     */
    public function getDealNotes(PipedriveAccount $pipedriveAccount, Project $project, Prospect $prospect, $dealId, Pipedrive $pipedrive)
    {
        $field = ProjectSetting::get($project, 'pipedrive.deal.notes.field');
            
        if (!$field) {
            return;
        }

        Config::set([
            'pipedrive.api_token' => $pipedriveAccount->token,
        ]); 

        // Get deal associated products
        // from pipedrive
        $response = $pipedrive->getDealNotes($dealId);
        if (!$response || !$response['success']) {
            return;
        }

        $notes = is_array($response['data']) ? $response['data'] : [];
        
        $content = implode("\n\n", array_map(function($note) {
            return $note['content'];
        }, $notes));

        if (Str::startsWith($field, 'meta->')) {
            $field = str_replace('meta->', '', $field);

            $value = $prospect->meta ?? [];
            $value[$field] = $content;
            $update = [
                "meta" => json_encode($value)
            ];
        } else {
            $update = [
                $field => $content
            ];
        }

        DB::table('prospects')
            ->where('id', $prospect->id)
            ->limit(1)
            ->update($update);
    }
    
    /**
     * Check mapped label
     */
    protected function getMappedLabel($project, $pipelineId, $stageId) {
        $stages = (array) ProjectSetting::get($project, 'pipedrive.stages.' . $pipelineId, []);

        if (!isset($stages[$stageId])) {
            return null;
        }

        return Label::find($stages[$stageId]);
    }
    
    /**
     * Find if a label having name $labelName
     * exists inside a labels category,
     * if not, create the label
     * 
     * @param  \App\Category  $category parent
     * @param  {string}  $labelName name of the label to find
     */
    protected function findOrCreateLabel($category, $labelName)
    {

        // Find if label already
        // exists in this category
        $label = $category
            ->labels
            ->where('name', $labelName)
            ->first();

        // If not,
        // we create a none validated label
        if (!$label) {
            $label = $this->createNewLabel($category, $labelName);
            // Reload labels list in the category
            $category->load('labels');
        }

        return $label;
    }
    
    /**
     * Create new label
     * inside a labels category
     * 
     * @param  \App\Category  $category parent
     * @param  {string}  $labelName name of the new label
     */
    protected function createNewLabel($category, $labelName)
    {
        return $category
            ->labels()
            ->create([
                'name' => $labelName,
                'color' => "#ffffff",
                'bgcolor' => $this->randColor(rand(250, 350)),
                'validated' => 0
            ]);
    }

    /**
     * Generate random color
     * 
     * @param  $sum
     */
    protected function randColor($sum = 300)
    {
        $r = rand(0, min($sum, 255));
        $g = rand(0, min($sum - $r, 255));
        $b = min($sum - $r - $g, 255);

        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }
}
