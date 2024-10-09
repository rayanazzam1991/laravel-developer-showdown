<?php

namespace App\Features\SyncUserAttributes\Application\Service;

use App\Features\SyncUserAttributes\Application\Contracts\ApiLimitsInterface;
use App\Features\SyncUserAttributes\Application\Events\UserAttributesBatchSending;
use App\Features\SyncUserAttributes\Application\Request\SyncUserAttributesRequest;
use App\Features\SyncUserAttributes\Domain\Enum\QueueDataStatusEnum;
use App\Features\SyncUserAttributes\Domain\Enum\SyncApiParametersEnum;
use App\Features\SyncUserAttributes\Infrastructure\Repository\Eloquent\UserAttributeQueueRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

readonly class SyncUserAttributesInteractor implements SyncUserAttributesInterface
{
    public function __construct(
        private UserAttributeQueueRepository $attributeQueueRepository,
        private ApiLimitsInterface $apiLimits
    ) {}

    public function prepareDataAndTriggerSyncProcess(): void
    {
        // Set batch data status to Pending
        $batchSize = SyncApiParametersEnum::MAX_RECORDS_PER_BATCH_REQUEST->value;
        $this->attributeQueueRepository->changeBatchDataToPending($batchSize);

        // trigger Sync Process in a queue
        UserAttributesBatchSending::dispatch();

    }

    public function syncReadyData(): void
    {
        Log::info('Start Batch Sync');
        // prepare the request
        $batchSize = SyncApiParametersEnum::MAX_RECORDS_PER_BATCH_REQUEST->value;

        $records = $this->attributeQueueRepository->getPendingRecords($batchSize);
        $request = SyncUserAttributesRequest::fromModel($records);

        // send the request to api
        $response = $this->callSendApi($request);

        // Update status to sent/failed
        $this->updateSentDataStatus($response);
    }

    public function syncMissedOrFailedData(): void
    {

        // prepare the request
        $batchSize = SyncApiParametersEnum::MAX_RECORDS_PER_BATCH_REQUEST->value;

        $records = $this->attributeQueueRepository->getFailedOrUnSentRecords($batchSize);

        if ($records->count() == 0) {
            Log::info('Nothing to send');

            return;
        }

        Log::info('Start Batch Sync for missed or previous failed to send before');
        $request = SyncUserAttributesRequest::fromModel($records);

        // send the request to api
        $response = $this->callSendApi($request);

        // Update status to sent/failed
        $this->updateFailedOrUnSentDataStatus($response);
    }

    private function callSendApi(SyncUserAttributesRequest $request): JsonResponse
    {
        // init the Api request limit
        $this->apiLimits->initBatchUsage();

        Log::info('Current Api usage before sending : ', [$this->apiLimits->getCurrentBatchUsage()]);

        if ($this->apiLimits->getCurrentBatchUsage() >= SyncApiParametersEnum::MAX_BATCH_RPH->value) {

            Log::info('User Attributes not synced, you reach the limit, you have to wait for the next hour!');

            return response()->json([
                'status' => 'error',
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }
        // fake the API Call
        Log::info('User Attributes Synced Successfully!');

        // increment api usage
        $this->apiLimits->incrementBatchUsage();

        Log::info('Current Api usage before sending : ', [$this->apiLimits->getCurrentBatchUsage()]);

        return response()->json([
            'status' => 'ok',
        ], Response::HTTP_OK);
    }

    private function updateSentDataStatus(JsonResponse $response): void
    {
        if ($response->status() == Response::HTTP_OK) {
            $this->attributeQueueRepository->updatePendingRecordsStatus(QueueDataStatusEnum::SENT->value);
        } else {
            $this->attributeQueueRepository->updatePendingRecordsStatus(QueueDataStatusEnum::FAILED->value);
        }
    }

    private function updateFailedOrUnSentDataStatus(JsonResponse $response): void
    {
        if ($response->status() == Response::HTTP_OK) {
            $this->attributeQueueRepository->updateFailedOrUnSentDataStatus(QueueDataStatusEnum::SENT->value);
        } else {
            $this->attributeQueueRepository->updateFailedOrUnSentDataStatus(QueueDataStatusEnum::FAILED->value);
        }
    }
}
