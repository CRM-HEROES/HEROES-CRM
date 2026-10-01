<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Utils\ProjectMail;
use App\Utils\ProjectSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
        if (in_array($setting, ['email', 'brevo']) && is_array($value)) {
            $value['validated_at'] = null;
        }

        ProjectSetting::set($project, $setting, $value);

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Validate the project email configuration in the background.
     */
    public function test(Project $project, $setting)
    {
        abort_unless(auth()->user()->can('', $project), 404);

        if ($setting === 'brevo') {
            return $this->testBrevo($project);
        }

        abort_unless($setting === 'email', 404);

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
     * Validate the project Brevo SMS configuration without sending an SMS.
     */
    protected function testBrevo(Project $project)
    {
        $settings = ProjectSetting::get($project, 'brevo');
        abort_unless($settings && data_get($settings, 'api_key') && data_get($settings, 'sender'), 422);

        try {
            $apiKey = data_get($settings, 'api_key');
            $headers = ['api-key' => $apiKey, 'accept' => 'application/json'];
            $accountResponse = Http::withHeaders($headers)->get('https://api.brevo.com/v3/account');

            if (!$accountResponse->successful()) {
                throw new \Exception($accountResponse->json('message', $accountResponse->body()));
            }

            $sendersResponse = Http::withHeaders($headers)->get('https://api.brevo.com/v3/senders');

            if (!$sendersResponse->successful()) {
                throw new \Exception($sendersResponse->json('message', $sendersResponse->body()));
            }

            $sender = strtolower(trim(data_get($settings, 'sender')));
            $senderExists = collect($sendersResponse->json('senders', []))->contains(function ($configuredSender) use ($sender) {
                return strtolower(trim(data_get($configuredSender, 'name'))) === $sender
                    || strtolower(trim(data_get($configuredSender, 'email'))) === $sender;
            });

            if (!$senderExists) {
                throw new \Exception('L’expéditeur Brevo configuré n’est pas autorisé.');
            }

            $settings->validated_at = now()->toIso8601String();
            ProjectSetting::set($project, 'brevo', $settings);

            return ['validated' => true, 'validated_at' => $settings->validated_at];
        } catch (\Throwable $exception) {
            $settings->validated_at = null;
            ProjectSetting::set($project, 'brevo', $settings);

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
