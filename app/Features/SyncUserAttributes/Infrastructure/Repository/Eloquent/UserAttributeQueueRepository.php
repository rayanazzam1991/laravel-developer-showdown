<?php

namespace App\Features\SyncUserAttributes\Infrastructure\Repository\Eloquent;

use App\Features\SyncUserAttributes\Domain\Enum\QueueDataStatusEnum;
use App\Features\SyncUserAttributes\Infrastructure\Repository\Eloquent\Model\UserAttributeQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UserAttributeQueueRepository
{
    /**
     * @param array{
     *  paylod:string,
     *  status:int,
     *  retry_count:int
     * } $data
     */
    public function store(array $data): void
    {
        UserAttributeQueue::query()->create($data);
    }

    public function changeBatchDataToPending(int $batchSize): void
    {

        UserAttributeQueue::query()->where(function (Builder $query) {
            $query->where(column: 'status',
                operator: '=',
                value: QueueDataStatusEnum::UN_SENT->value);
        })
            ->orderBy('created_at')
            ->limit($batchSize)
            ->update([
                'status' => QueueDataStatusEnum::PENDING->value,
            ]);
    }

    public function updatePendingRecordsStatus(int $status): void
    {
        UserAttributeQueue::query()->where(
            column: 'status',
            operator: '=',
            value: QueueDataStatusEnum::PENDING->value
        )
            ->orderBy(column: 'created_at')
            ->update([
                'status' => $status,
            ]);
    }

    public function updateFailedOrUnSentDataStatus(int $status): void
    {
        UserAttributeQueue::query()->where(function (Builder $query) {
            $query->where(
                column: 'status',
                operator: '=',
                value: QueueDataStatusEnum::UN_SENT->value
            )->orWhere(
                column: 'status',
                operator: '=',
                value: QueueDataStatusEnum::FAILED->value
            );
        })
            ->orderBy(column: 'created_at')
            ->update([
                'status' => $status,
            ]);
    }

    public function getUnsentRecordsSize(): int
    {
        return UserAttributeQueue::query()
            ->where(function (Builder $query) {
                $query->where(column: 'status',
                    operator: '=',
                    value: QueueDataStatusEnum::UN_SENT->value);
            })
            ->orderBy('created_at', 'asc')
            ->count();
    }

    /**
     * @return Collection<int,UserAttributeQueue>
     */
    public function getPendingRecords(int $batchSize): Collection
    {
        return UserAttributeQueue::query()
            ->where(
                column: 'status',
                operator: '=',
                value: QueueDataStatusEnum::PENDING->value)
            ->orderBy('created_at')
            ->limit($batchSize)
            ->get();
    }

    /**
     * @return Collection<int,UserAttributeQueue>
     */
    public function getFailedOrUnSentRecords(int $batchSize): Collection
    {
        return UserAttributeQueue::query()
            ->where(function (Builder $query) {
                $query->where(
                    column: 'status',
                    operator: '=',
                    value: QueueDataStatusEnum::UN_SENT->value
                )
                    ->orWhere(
                        column: 'status',
                        operator: '=',
                        value: QueueDataStatusEnum::FAILED->value
                    );
            })
            ->orderBy('created_at')
            ->limit($batchSize)
            ->get();
    }

    public function removeSentRecords(): void
    {
        UserAttributeQueue::query()
            ->where(
                column: 'status',
                operator: '=',
                value: QueueDataStatusEnum::SENT->value
            )
            ->delete();
    }
}
