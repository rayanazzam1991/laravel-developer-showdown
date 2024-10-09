<?php

namespace App\Features\SyncUserAttributes\Application\Contracts;

interface ApiLimitsInterface
{
    public function initBatchUsage(): void;

    public function resetBatchUsage(): void;

    public function getCurrentBatchUsage(): int;

    public function incrementBatchUsage(): void;
}
