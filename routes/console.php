<?php

use App\Features\SyncUserAttributes\Application\Service\ManageUserAttributeChanges;
use App\Features\SyncUserAttributes\Application\Service\SyncUserAttributesInterface;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    $service = resolve(ManageUserAttributeChanges::class);
    $service->removeSyncedUserAttributes();
})->everyTwoHours();

Schedule::call(function () {
    $service = resolve(SyncUserAttributesInterface::class);
    $service->syncMissedOrFailedData();
})->hourly();
