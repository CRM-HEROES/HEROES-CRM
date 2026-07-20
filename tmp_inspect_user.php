<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::find(3);
echo $user->id . '|' . $user->name . '|role=' . $user->role . '|roles=';
foreach ($user->roles as $role) {
    echo $role->name . '|';
}
echo PHP_EOL;
