<?php

namespace App\Features\SyncUserAttributes\Application\Service;

interface SyncUserAttributesInterface
{
    public function prepareDataAndTriggerSyncProcess(): void;

    public function syncReadyData(): void;

    public function syncMissedOrFailedData(): void;
}
