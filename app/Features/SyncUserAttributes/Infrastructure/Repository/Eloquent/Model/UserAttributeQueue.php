<?php

namespace App\Features\SyncUserAttributes\Infrastructure\Repository\Eloquent\Model;

use App\Features\SyncUserAttributes\Application\Events\UserAttributesBatchReady;
use App\Features\SyncUserAttributes\Domain\Enum\SyncApiParametersEnum;
use App\Features\SyncUserAttributes\Infrastructure\Repository\Eloquent\Trait\UserAttributeTrait;
use Database\Factories\UserAttributeQueueFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $payload
 * @property int $status
 * @property int $retry_count
 *
 * @method static Builder|UserAttributeQueue whereBatchId($value)
 * @method static Builder|UserAttributeQueue whereStatus($value)
 * @method static Builder|UserAttributeQueue whereRetryCount($value)
 *
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Database\Factories\UserAttributeQueueFactory factory($count = null, $state = [])
 * @method static Builder|UserAttributeQueue newModelQuery()
 * @method static Builder|UserAttributeQueue newQuery()
 * @method static Builder|UserAttributeQueue query()
 * @method static Builder|UserAttributeQueue whereCreatedAt($value)
 * @method static Builder|UserAttributeQueue whereId($value)
 * @method static Builder|UserAttributeQueue wherePayload($value)
 * @method static Builder|UserAttributeQueue whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class UserAttributeQueue extends Model
{
    /**
     * @use HasFactory<UserAttributeQueueFactory>
     */
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
                UserAttributesBatchReady::dispatch();
            }

        });
    }
}
