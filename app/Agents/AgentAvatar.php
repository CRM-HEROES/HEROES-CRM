<?php

namespace App\Agents;

use App\Models\Agent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * The picture of an agent.
 *
 * The uploaded file is never kept: it is decoded, cropped to a square, resized
 * and written again as a PNG. Whatever the original was (a renamed script, an
 * image with hidden data, an oversized bomb) only the pixels survive. The file
 * gets a random name, which is also what the public address of the picture
 * contains: the picture of an agent is shown like the logo of a project.
 */
class AgentAvatar
{
    public const DISK = 'agent_avatars';
    public const SIZE = 256;
    public const MAX_BYTES = 2 * 1024 * 1024;
    // A picture that would need more memory than this to decode is refused
    public const MAX_PIXELS = 25_000_000;
    public const TYPES = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];

    /**
     * @throws AgentRequestException when the file is not an acceptable picture
     */
    public function store(Agent $agent, UploadedFile $file): string
    {
        if (!$file->isValid() || $file->getSize() > self::MAX_BYTES) {
            throw new AgentRequestException('La photo est trop lourde (2 Mo au plus).');
        }

        $path = $file->getRealPath();
        $info = @getimagesize($path);

        if (!$info || !in_array($info[2], self::TYPES, true)) {
            throw new AgentRequestException('La photo doit être une image JPEG, PNG, GIF ou WebP.');
        }

        if ($info[0] * $info[1] > self::MAX_PIXELS) {
            throw new AgentRequestException('La photo est trop grande en pixels.');
        }

        $source = @imagecreatefromstring((string) file_get_contents($path));

        if (!$source) {
            throw new AgentRequestException("Cette photo ne peut pas être lue.");
        }

        $png = $this->square($source);
        $name = Str::random(40) . '.png';

        $disk = Storage::disk(self::DISK);
        $previous = $agent->avatar;
        $disk->put($name, $png);

        $agent->update(['avatar' => $name]);

        if ($previous) {
            $disk->delete($previous);
        }

        return $name;
    }

    public function remove(Agent $agent): void
    {
        if ($agent->avatar) {
            Storage::disk(self::DISK)->delete($agent->avatar);
            $agent->update(['avatar' => null]);
        }
    }

    /**
     * Public address of the picture (null: the agent has none).
     */
    public static function url(?string $avatar): ?string
    {
        return $avatar ? '/api/agent-avatar/' . pathinfo($avatar, PATHINFO_FILENAME) : null;
    }

    /**
     * The file behind a token of the public address, or null. Only the 40
     * letters and digits of a name we generated are accepted: no path.
     */
    public static function find(string $token): ?string
    {
        if (!preg_match('/^[A-Za-z0-9]{40}$/', $token)) {
            return null;
        }

        $name = $token . '.png';

        return Storage::disk(self::DISK)->exists($name) ? $name : null;
    }

    /**
     * Centered square crop, SIZE x SIZE, as PNG bytes
     */
    protected function square($source): string
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $side = min($width, $height);

        $target = imagecreatetruecolor(self::SIZE, self::SIZE);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagefill($target, 0, 0, imagecolorallocatealpha($target, 255, 255, 255, 127));

        imagecopyresampled($target, $source, 0, 0, intdiv($width - $side, 2), intdiv($height - $side, 2), self::SIZE, self::SIZE, $side, $side);

        ob_start();
        imagepng($target, null, 6);
        $bytes = (string) ob_get_clean();

        return $bytes;
    }
}
