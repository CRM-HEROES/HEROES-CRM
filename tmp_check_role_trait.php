<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

foreach ([2,3,4] as $id) {
    $u = User::find($id);
    echo 'user=' . $id . '|name=' . $u->name . '|hasRoleAgent=' . ($u->hasRole('Agent commercial') ? 'yes' : 'no') . '|hasRoleAssistant=' . ($u->hasRole('Assistant commercial') ? 'yes' : 'no') . '|getRoleNames=' . $u->getRoleNames()->implode(',') . PHP_EOL;
}
