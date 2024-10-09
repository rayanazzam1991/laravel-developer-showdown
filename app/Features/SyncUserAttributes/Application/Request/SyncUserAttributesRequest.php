<?php

namespace App\Features\SyncUserAttributes\Application\Request;

use App\Features\SyncUserAttributes\Infrastructure\Repository\Eloquent\Model\UserAttributeQueue;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Data;

class SyncUserAttributesRequest extends Data
{
    /**
     * @param  array<int, array<string, array<string>>>  $batches
     */
    public function __construct(
        public array $batches
    ) {}

    /**
     * @param  Collection<int, UserAttributeQueue>  $records
     */
    public static function fromModel(Collection $records): self
    {
        $batches = [];
        $subscribers = [];
        foreach ($records as $record) {
            // Ensure that payload is cast to a string
            $subscribers[] = (string) $record->payload;
            $batches[] = [
                'subscribers' => $subscribers,
            ];
        }

        return new self(
            batches: $batches
        );
    }
}
