<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$date = '2026-07-12';
$rows = DB::table('attendances')->whereDate('date', $date)->get();

echo 'attendance_rows=' . $rows->count() . PHP_EOL;
foreach ($rows as $row) {
    echo $row->id . '|' . $row->user_id . '|' . $row->project_id . '|' . $row->status . '|' . $row->date . PHP_EOL;
}
