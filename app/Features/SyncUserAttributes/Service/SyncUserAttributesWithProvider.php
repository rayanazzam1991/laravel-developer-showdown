<?php

namespace App\Features\SyncUserAttributes\Service;

use App\Features\SyncUserAttributes\Enum\QueueDataStatusEnum;
use App\Features\SyncUserAttributes\Enum\SyncApiParametersEnum;
use App\Features\SyncUserAttributes\Events\UserAttributesBatchSending;
use App\Features\SyncUserAttributes\Repository\UserAttributeQueueRepository;
use App\Features\SyncUserAttributes\Request\SyncUserAttributesRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

readonly class SyncUserAttributesWithProvider
{
    public function __construct(
        private UserAttributeQueueRepository $attributeQueueRepository
    )
    {
    }

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
        Log::info("sending");
        // prepare the request
        $batchSize = SyncApiParametersEnum::MAX_RECORDS_PER_BATCH_REQUEST->value;

        $records = $this->attributeQueueRepository->getPendingRecords($batchSize);
        $request = SyncUserAttributesRequest::fromModel($records);

        // send the request to api
        $response = $this->callSendApi($request);

        // Update status to sent/failed
        $this->updateSentDataStatus($response, $batchSize);
    }

    private function callSendApi(SyncUserAttributesRequest $request): JsonResponse
    {
        // fake the API Call
        Log::info('updated User');
        return response()->json([
            'status' => 'ok'
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

}
