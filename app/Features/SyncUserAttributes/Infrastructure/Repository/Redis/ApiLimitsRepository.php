<?php

namespace App\Features\SyncUserAttributes\Infrastructure\Repository\Redis;

use App\Features\SyncUserAttributes\Application\Contracts\ApiLimitsInterface;
use Illuminate\Support\Facades\Redis;

class ApiLimitsRepository implements ApiLimitsInterface
{
    public function initBatchUsage(): void
    {
        $batchKey = $this->getBatchKey();
        // Check if the key exists
        if (! Redis::exists($batchKey)) {
            // Set the key with an initial value of 0 and a TTL of 1 hour (3600 seconds)
            Redis::set($batchKey, 0);
            Redis::expire($batchKey, 3600); // Set expiration for 1 hour
        }
    }

    public function getCurrentBatchUsage(): int
    {
        $batchKey = $this->getBatchKey();

        // If the key exists, return its value. Otherwise, return 0.
        if (Redis::exists($batchKey)) {
            $value = Redis::get($batchKey);

            // check if the value is int
            if (is_numeric($value)) {
                return (int) $value;
            }
        }

        return 0;

    }

    public function incrementBatchUsage(): void
    {
        $batchKey = $this->getBatchKey();

        // Initialize the key if it doesn't exist yet
        $this->initBatchUsage();

        // Increment the batch count by 1
        Redis::incr($batchKey);
    }

    // Helper function to get the current batch key based on the hour
    private function getBatchKey(): string
    {
        return 'api_usage:batch_request:'.date('Y-m-d-H');
    }
}
