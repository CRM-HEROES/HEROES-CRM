<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Console\Commands\AttendanceAuto;
use App\Models\User;
use Illuminate\Support\Str;
use ReflectionMethod;

$command = new AttendanceAuto();
$method = new ReflectionMethod($command, 'allowedUsers');
$method->setAccessible(true);
$users = $method->invoke($command);

echo 'allowed_users=' . count($users) . PHP_EOL;
foreach ($users as $u) {
    echo $u->id . '|' . $u->name . '|roles=' . $u->roles->pluck('name')->implode(',') . '|projects=' . $u->projects->count() . PHP_EOL;
}
