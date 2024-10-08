<?php

namespace App\Features\SyncUserAttributes\Request;


use App\Features\SyncUserAttributes\Repository\Eloquent\Model\UserAttributeQueue;
use App\Features\SyncUserAttributes\Service\SyncUserAttributesWithProvider;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Data;

class SyncUserAttributesRequest extends Data
{

    public function __construct(
        public array $batches
    )
    {

    }

    public static function fromModel(Collection $records): self
    {
        $batches = [];
        $subscribers = [];
        foreach ($records as $record) {
            $subscribers[] = $record->payload;
            $batches[] = [
                "subscribers" => $subscribers
            ];
        }
        return new self(
            batches: $batches
        );
    }
}
