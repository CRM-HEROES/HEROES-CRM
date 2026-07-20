<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

$users = User::with(['projects'])->get();

foreach ($users as $u) {
    $roleNames = DB::table('model_has_roles')
        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->where('model_has_roles.model_type', User::class)
        ->where('model_has_roles.model_id', $u->id)
        ->pluck('roles.name')
        ->map(fn($name) => trim(Str::lower((string) $name)))
        ->all();

    $hasAllowedRole = collect($roleNames)->contains(fn($roleName) => in_array($roleName, ['commercial', 'agent commercial', 'assistant commercial'], true));

    echo $u->id . '|' . $u->name . '|db_roles=' . implode(',', $roleNames) . '|has_allowed=' . ($hasAllowedRole ? 'yes' : 'no') . '|projects=' . $u->projects->count() . PHP_EOL;
}

echo '--- roles ---' . PHP_EOL;
foreach (DB::table('roles')->get() as $row) {
    echo $row->id . '|' . $row->name . '|' . $row->guard_name . PHP_EOL;
}
