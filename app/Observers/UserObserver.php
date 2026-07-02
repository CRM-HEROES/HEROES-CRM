<?php

namespace App\Observers;

use App\Jobs\ImportProspects;
use App\Models\User;
use App\Models\UserSetting;
use App\Notifications\UserRegistration;
use Illuminate\Support\Facades\DB;
use Stevebauman\Location\Facades\Location;

class UserObserver
{
    /**
     * Handle the Thread "created" event.
     */
    public function created(User $user): void
    {
        if (is_array($user->default_projects)) {
            if (in_array('demo', $user->default_projects)) {
                $this->createDemoProject($user);
            }
            
            if (in_array('personal', $user->default_projects)) {
                $this->createPersonalProject($user);
            }
            
            if (in_array('professional', $user->default_projects)) {
                $this->createProfessionalProject($user);
            }
        }

        $user->notify(new UserRegistration());
    }
    
    /**
     * Handle the Thread "updated" event.
     */
    public function updated(User $user): void
    {
        $this->getLocation($user);
    }
    
    /**
     * Create demo project
     *
     * @param  \App\Models\Thread  $thread
     * @return void
     */
    protected function createDemoProject(User $user)
    {
        $demoProject = $user->projects()->create([
            'type' => "demo",
            'name' => trans('project.type.demo.name'),
            'description' => trans('project.type.demo.description'),
            'creator_id' => $user->id
        ]);

        $demoProject->users()->detach();
        $demoProject->users()->syncWithoutDetaching($user->id);

        $setting = [["key"=>"email","width"=>215],["key"=>"first_name"],["key"=>"last_name"],["key"=>"company_name","width"=>190],["key"=>"job_title"],["key"=>"website_url","width"=>185],["key"=>"street","width"=>180],["key"=>"state","width"=>100],["key"=>"postal_code","width"=>110],["key"=>"city"],["key"=>"county","width"=>150],["key"=>"country","width"=>80],["key"=>"phone_number","width"=>100],["key"=>"mobile_phone_number","width"=>100],["key"=>"latitude"],["key"=>"longitude"]];

        $categoryStatus = $demoProject->categories()->where('name', trans('label.category.status.name'))->first();
        if ($categoryStatus) {
            $setting = array_values(array_merge([["key"=>"category->" . $categoryStatus->id]], $setting));
        }

        $demoImport = $demoProject->imports()->create([
            'name' => "Demo",
            'path' => "demo.xlsx",
            'mapping' => array_map(function($setting) {
                return $setting['key'];
            }, $setting),
            'source' => "file"
        ]);

        DB::table('imports')
            ->where('id', $demoImport->id)
            ->update([
                'is_processing' => true
            ]);

        // try {
            $demoImport->refresh();
            ImportProspects::dispatchSync($demoImport);
            $demoImport->refresh();

            UserSetting::updateOrCreate([
                'project_id' => $demoProject->id,
                'user_id' => $user->id,
                'key' => 'prospects-table',
            ], [
                'value' => array_filter($setting, function($setting) {
                    return $setting['key'] != 'latitude' && $setting['key'] != 'longitude';
                }),
                'creator_id' => null,
            ]);
            
        //} finally {
            $demoImport->delete();
        //}
    }
    
    /**
     * Create demo project
     *
     * @param  \App\Models\Thread  $thread
     * @return void
     */
    protected function createPersonalProject(User $user)
    {
        $personalProject = $user->projects()->create([
            'type' => "personal",
            'name' => trans('project.type.personal.name'),
            'description' => trans('project.type.personal.description'),
            'creator_id' => $user->id
        ]);
        
        $personalProject->users()->detach();
        $personalProject->users()->syncWithoutDetaching($user->id);
    }
    
    /**
     * Create demo project
     *
     * @param  \App\Models\Thread  $thread
     * @return void
     */
    protected function createProfessionalProject(User $user)
    {
        $professionalProject = $user->projects()->create([
            'type' => "professional",
            'name' => trans('project.type.professional.name'),
            'description' => trans('project.type.professional.description'),
            'creator_id' => $user->id
        ]);
        
        $professionalProject->users()->detach();
        $professionalProject->users()->syncWithoutDetaching($user->id);
    }

    /**
     * Get user location
     * from his IP address
     *
     * @param  \App\Models\Thread  $thread
     * @return void
     */
    protected function getLocation(User $user)
    {
        if (!$user->isDirty('ip_address')) {
            return;
        }

        $user->syncOriginal(['ip_address']);

        // $ip = "115.99.96.19";
        $ip = $user->ip_address;
        $address = Location::get($ip);

        if (!$address) {
            return;
        }

        $user->update([
            'ip_country' => $address->countryName,
            'ip_state' => $address->regionName,
            'ip_city' => $address->cityName,
            'ip_postal_code' => $address->postalCode ? $address->postalCode : $address->zipCode,
            'ip_latitude' => $address->latitude,
            'ip_longitude' => $address->longitude,
        ]);

        /*
        {#2168 // app\Observers\UserObserver.php:154
        +ip: "115.99.96.19"
        +driver: "Stevebauman\Location\Drivers\IpApi"
        +currencyCode: "INR"
        +countryCode: "IN"
        +regionCode: "KA"

        ++++ countryName: "India"
        ++++ regionName: "Karnataka"
        ++++ cityName: "Bengaluru"
        ++++ zipCode: "562130"
        ++++ postalCode: null
        ++++ latitude: "12.9634"
        ++++ longitude: "77.5855"

        +isoCode: null
        +metroCode: null
        +areaCode: "KA"
        +timezone: "Asia/Kolkata"
        }*/
    }
    
}
