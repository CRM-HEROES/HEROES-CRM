<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Utils\ProjectMail;
use App\Utils\ProjectSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SettingController extends Controller
{
    protected $allowViewForAll = ['map.color'];

    /**
     * Display the specified resource.
     */
    public function show(Project $project, $setting)
    {
        abort_unless(auth()->user()->can('', $project) || in_array($setting, $this->allowViewForAll), 404);

        return ProjectSetting::get($project, $setting, config('default-settings.' . $setting, null));
    }

    /**
     * Check if setting is set
     */
    public function check(Project $project, $setting)
    {
        // Allow for all

        return ProjectSetting::get($project, $setting, null) != null;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, $setting)
    {
        abort_unless(auth()->user()->can('', $project), 404);

        $this->validate($request, [
            'value' => 'required',
        ]);

        $value = $request->input('value');
        if ($setting === 'email' && is_array($value)) {
            $value['validated_at'] = null;
        }

        ProjectSetting::set($project, $setting, $value);

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Validate the project email configuration in the background.
     */
    public function testEmail(Project $project)
    {
        abort_unless(auth()->user()->can('', $project), 404);

        $settings = ProjectSetting::get($project, 'email');
        abort_unless($settings && data_get($settings, 'from.address'), 422);

        try {
            ProjectMail::configure($project);
            Mail::raw('Test de configuration email Brevo pour ' . $project->name, function ($message) use ($settings) {
                $message->to(data_get($settings, 'from.address'))
                    ->subject('Test de configuration email - HeroesCRM');
            });

            $settings->validated_at = now()->toIso8601String();
            ProjectSetting::set($project, 'email', $settings);

            return ['validated' => true, 'validated_at' => $settings->validated_at];
        } catch (\Throwable $exception) {
            $settings->validated_at = null;
            ProjectSetting::set($project, 'email', $settings);

            return response()->json([
                'validated' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, $setting)
    {
        ProjectSetting::delete($project, $setting);

        return ['message' => trans('common.success.deleted_resource')];
    }
}
