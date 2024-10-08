<?php

namespace App\Features\SyncUserAttributes\Repository\Eloquent\Model;

use App\Features\SyncUserAttributes\Enum\QueueDataStatusEnum;
use App\Features\SyncUserAttributes\Enum\SyncApiParametersEnum;
use App\Features\SyncUserAttributes\Events\UserAttributesBatchReady;
use App\Features\SyncUserAttributes\Repository\Eloquent\Trait\UserAttributeTrait;
use App\Features\SyncUserAttributes\Repository\UserAttributeQueueRepository;
use App\Features\SyncUserAttributes\Service\SyncUserAttributesWithProvider;
use Database\Factories\UserAttributeQueueFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @property int|mixed $id
 * @property string|mixed $payload
 * @property int|mixed $status
 * @property int|mixed $retry_count
 * @method static Builder|UserAttributeQueue whereBatchId($value)
 * @method static Builder|UserAttributeQueue whereStatus($value)
 * @method static Builder|UserAttributeQueue whereRetryCount($value)
 */
class UserAttributeQueue extends Model
{
    use HasFactory, UserAttributeTrait;

    protected $table = 'user_attributes_queue';

    protected $guarded = [];


    protected static function newFactory(): UserAttributeQueueFactory
    {
        return UserAttributeQueueFactory::new();
    }

    protected static function boot(): void
    {
        parent::boot();

        static::created(function (UserAttributeQueue $model) {

            // Check if the batch is ready
            $isReady = $model->unSentRecordsCount() >= SyncApiParametersEnum::MAX_RECORDS_PER_BATCH_REQUEST->value;

            if ($isReady) {
                // Dispatch the batch ready event
                UserAttributesBatchReady::dispatch($model);
            }

        });
    }

}
