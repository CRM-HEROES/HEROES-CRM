<?php

use App\Http\Controllers\Webhook\AircallController;
use App\Http\Controllers\Webhook\RingoverController;
use App\Http\Controllers\Webhook\Project\PipedriveController;

use Illuminate\Support\Facades\Route;

Route::post('aircall', [AircallController::class, 'store'])->name('aircall.store');
Route::post('ringover', [RingoverController::class, 'store'])->name('ringover.store');
Route::post('project/{project}/pipedrive/person/store', [PipedriveController::class, 'personStore'])->name('project.pipedrive.person.store');
Route::post('project/{project}/pipedrive/person/update', [PipedriveController::class, 'personUpdate'])->name('project.pipedrive.person.update');
Route::post('project/{project}/pipedrive/deal/store', [PipedriveController::class, 'dealStore'])->name('project.pipedrive.deal.store');
Route::post('project/{project}/pipedrive/deal/update', [PipedriveController::class, 'dealUpdate'])->name('project.pipedrive.deal.update');