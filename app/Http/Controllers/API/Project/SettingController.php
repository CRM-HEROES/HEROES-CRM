<?php

namespace App\Http\Controllers\API\Project;

use App\Calls\CallSettings;
use App\Calls\CallEligibility;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Project;
use App\Utils\ProjectMail;
use App\Utils\ProjectSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SettingController extends Controller
{
    protected $allowViewForAll = ['map.color'];

    /**
     * Display the specified resource.
     */
    public function show(Project $project, $setting)
    {
        if ($setting === 'calls') {
            abort_unless(auth()->user()->can('projectRoleUpdate', $project), 404);

            return $this->showCalls($project);
        }

        abort_unless(auth()->user()->can('', $project) || in_array($setting, $this->allowViewForAll), 404);

        return ProjectSetting::get($project, $setting, config('default-settings.' . $setting, null));
    }

    /**
     * Check if setting is set
     */
    public function check(Project $project, $setting)
    {
        if ($setting === 'calls') {
            abort_unless(auth()->user()->can('projectRoleUpdate', $project), 404);
        }

        // Allow for all

        return ProjectSetting::get($project, $setting, null) != null;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, $setting)
    {
        if ($setting === 'calls') {
            abort_unless(auth()->user()->can('projectRoleUpdate', $project), 404);

            return $this->updateCalls($request, $project);
        }

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

    public function diagnoseCalls(Request $request, Project $project, CallEligibility $eligibility)
    {
        abort_unless(auth()->user()->can('projectRoleUpdate', $project), 404);
        $validated = $request->validate([
            'prospect_id' => 'required|integer|min:1',
        ]);
        $prospect = $project->prospects()->findOrFail($validated['prospect_id']);
        $checks = $eligibility->smsChecks(
            $prospect,
            $project,
            null,
            auth()->id(),
            'cloudtalk'
        );
        $timezone = app(\App\Calls\WorkingHours::class)->timezone($prospect);
        foreach ($checks as &$check) {
            if ($check['reason'] === 'outside_calling_hours') {
                $check['message'] = $eligibility->smsMessage('outside_calling_hours', $prospect);
            }
        }
        unset($check);

        return [
            'prospect_id' => $prospect->id,
            'timezone' => $timezone,
            'checks' => $checks,
        ];
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
            $accountResponse = Http::timeout(15)->connectTimeout(5)->withHeaders($headers)->get('https://api.brevo.com/v3/account');

            if (!$accountResponse->successful()) {
                throw new \Exception($accountResponse->json('message', $accountResponse->body()));
            }

            $sendersResponse = Http::timeout(15)->connectTimeout(5)->withHeaders($headers)->get('https://api.brevo.com/v3/senders');

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
        if ($setting === 'calls') {
            abort_unless(auth()->user()->can('projectRoleUpdate', $project), 404);
        }

        ProjectSetting::delete($project, $setting);

        return ['message' => trans('common.success.deleted_resource')];
    }

    protected function showCalls(Project $project): array
    {
        $settings = (array) ProjectSetting::get($project, 'calls', []);
        $workHours = (array) data_get($settings, 'work_hours', config('calls.work_hours', []));

        return [
            'global_enabled' => CallSettings::globalEnabled(),
            'can_toggle_global' => (bool) auth()->user()->is_super_admin,
            'calls_enabled' => (bool) $project->calls_enabled,
            'provider' => CallSettings::provider($project),
            'timezone' => data_get($settings, 'timezone', config('app.timezone')),
            'work_hours' => [
                'start' => $workHours['start'] ?? '08:00',
                'end' => $workHours['end'] ?? '17:00',
            ],
            'work_days' => data_get($settings, 'work_days', config('calls.work_days', [1, 2, 3, 4, 5])),
            'greeting' => data_get($settings, 'greeting', ''),
            'transfer_available' => (bool) data_get($settings, 'transfer_available', false),
            'call_agent_id' => $project->call_agent_id,
            'call_transfer_user_id' => $project->call_transfer_user_id,
            'call_back_label_id' => $project->call_back_label_id,
            'options' => [
                'agents' => Agent::query()->where('project_id', $project->id)->orderBy('name')->get(['id', 'name']),
                'users' => $project->users()->orderBy('name')->get(['users.id', 'users.name', 'users.last_name']),
                'labels' => DB::table('labels')
                    ->join('categories', 'categories.id', '=', 'labels.category_id')
                    ->where('categories.project_id', $project->id)
                    ->where('categories.for', 'prospect')
                    ->whereNull('categories.deleted_at')
                    ->whereNull('labels.deleted_at')
                    ->orderBy('labels.name')
                    ->get(['labels.id', 'labels.category_id', 'labels.name']),
            ],
        ];
    }

    protected function updateCalls(Request $request, Project $project): array
    {
        $value = $request->validate([
            'value' => 'required|array',
            'value.global_enabled' => 'sometimes|boolean',
            'value.calls_enabled' => 'sometimes|boolean',
            'value.provider' => 'sometimes|in:fake,cloudtalk,ai_voice',
            'value.work_hours' => 'sometimes|array',
            'value.work_hours.start' => 'required_with:value.work_hours|date_format:H:i',
            'value.work_hours.end' => 'required_with:value.work_hours|date_format:H:i',
            'value.work_days' => 'sometimes|array',
            'value.work_days.*' => 'integer|between:1,7',
            'value.timezone' => 'sometimes|timezone',
            'value.greeting' => 'sometimes|nullable|string|max:4000',
            'value.transfer_available' => 'sometimes|boolean',
            'value.call_agent_id' => 'sometimes|nullable|integer',
            'value.call_transfer_user_id' => 'sometimes|nullable|integer',
            'value.call_back_label_id' => 'sometimes|nullable|integer',
        ])['value'];

        if (array_key_exists('global_enabled', $value)) {
            abort_unless(auth()->user()->is_super_admin, 404);
            CallSettings::setGlobalEnabled((bool) $value['global_enabled']);
        }

        if (!empty($value['call_agent_id'])) {
            abort_unless(Agent::query()->where('project_id', $project->id)->whereKey($value['call_agent_id'])->exists(), 422);
        }
        if (!empty($value['call_transfer_user_id'])) {
            abort_unless($project->users()->whereKey($value['call_transfer_user_id'])->exists(), 422);
        }
        if (!empty($value['call_back_label_id'])) {
            abort_unless(DB::table('labels')
                ->join('categories', 'categories.id', '=', 'labels.category_id')
                ->where('categories.project_id', $project->id)
                ->where('categories.for', 'prospect')
                ->whereNull('categories.deleted_at')
                ->whereNull('labels.deleted_at')
                ->where('labels.id', $value['call_back_label_id'])
                ->exists(), 422);
        }

        $projectFields = array_intersect_key($value, array_flip([
            'calls_enabled',
            'call_agent_id',
            'call_transfer_user_id',
            'call_back_label_id',
        ]));
        if ($projectFields) {
            $project->update($projectFields);
        }

        $stored = (array) ProjectSetting::get($project, 'calls', []);
        foreach (['provider', 'work_hours', 'work_days', 'timezone', 'greeting', 'transfer_available'] as $key) {
            if (array_key_exists($key, $value)) {
                $stored[$key] = $value[$key];
            }
        }
        ProjectSetting::set($project, 'calls', $stored);

        return ['message' => trans('common.success.updated_resource')];
    }
}
