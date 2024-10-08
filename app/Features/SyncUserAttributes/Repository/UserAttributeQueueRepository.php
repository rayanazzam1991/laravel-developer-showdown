<?php

namespace App\Features\SyncUserAttributes\Repository;

use App\Features\SyncUserAttributes\Enum\QueueDataStatusEnum;
use App\Features\SyncUserAttributes\Enum\SyncApiParametersEnum;
use App\Features\SyncUserAttributes\Repository\Eloquent\Model\UserAttributeQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UserAttributeQueueRepository
{
    public function store(array $data): void
    {
        UserAttributeQueue::query()->create($data);
    }

    public function updateByBatchId(int $batchId, array $data): void
    {
        UserAttributeQueue::whereBatchId($batchId)
            ->update($data);
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
                'status' => QueueDataStatusEnum::PENDING->value
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
                'status' => $status
            ]);
    }

    public function isBatchBeingSent($batchId): bool
    {
        return UserAttributeQueue::whereBatchId($batchId)
            ->where('status', QueueDataStatusEnum::PENDING->value)
            ->exists();
    }

    public function getDataByBatch(int $batchId): Collection
    {
        return UserAttributeQueue::whereBatchId($batchId)
            ->get();
    }

    public function getLastInsertedId(): int
    {
        return UserAttributeQueue::query()->latest('id');
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

    public function getBatchRecordsSize(): int
    {
        return UserAttributeQueue::query()
            ->where(function (Builder $query) {
                $query->where(column: 'status',
                    operator: '=',
                    value: QueueDataStatusEnum::UN_SENT->value)
                    ->orWhere(column: 'status',
                        operator: '=',
                        value: QueueDataStatusEnum::PENDING->value);
            })
            ->count();
    }

    public function getUnsentRecords(int $limit): Collection
    {
        return UserAttributeQueue::query()
            ->where(
                column: 'status',
                operator: '=',
                value: QueueDataStatusEnum::UN_SENT->value)
            ->offset(0)
            ->limit($limit)
            ->get();

    }

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

}
