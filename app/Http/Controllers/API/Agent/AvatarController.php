<?php

namespace App\Http\Controllers\API\Agent;

use App\Agents\AgentAvatar;
use App\Agents\AgentRequestException;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * The picture of an agent: upload and removal by its owner, and the public
 * address the picture is shown at (a random name, like the logo of a project).
 */
class AvatarController extends Controller
{
    public function __construct(protected AgentAvatar $avatars)
    {
    }

    public function store(Request $request, int $agent)
    {
        $agent = $this->own($request, $agent);

        // Size and content are checked on the file itself (AgentAvatar), not on what the browser says
        $request->validate(['photo' => 'required|file']);

        try {
            $this->avatars->store($agent, $request->file('photo'));
        } catch (AgentRequestException $e) {
            return response(['message' => $e->getMessage(), 'errors' => ['photo' => [$e->getMessage()]]], 422);
        }

        return ['avatar_url' => AgentAvatar::url($agent->fresh()->avatar)];
    }

    public function destroy(Request $request, int $agent)
    {
        $this->avatars->remove($this->own($request, $agent));

        return ['avatar_url' => null];
    }

    /**
     * Public: shown in an <img>, which does not carry the session
     */
    public function show(string $token)
    {
        $name = AgentAvatar::find($token);

        abort_unless($name, 404);

        return response(Storage::disk(AgentAvatar::DISK)->get($name))->withHeaders([
            'Content-Type' => 'image/png',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    protected function own(Request $request, int $id): Agent
    {
        return Agent::query()->where('user_id', $request->user()->id)->findOrFail($id);
    }
}
