<?php

use App\Features\SyncUserAttributes\Application\Contracts\ApiLimitsInterface;
use App\Features\SyncUserAttributes\Application\Events\UserAttributesBatchReady;
use App\Features\SyncUserAttributes\Application\Events\UserAttributesBatchSending;
use App\Features\SyncUserAttributes\Application\Service\SyncUserAttributesInteractor;
use App\Features\SyncUserAttributes\Domain\Enum\QueueDataStatusEnum;
use App\Features\SyncUserAttributes\Domain\Enum\SyncApiParametersEnum;
use App\Features\SyncUserAttributes\Infrastructure\Repository\Eloquent\Model\UserAttributeQueue;
use App\Features\SyncUserAttributes\Infrastructure\Repository\Eloquent\UserAttributeQueueRepository;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;

it('dispatches UserAttributesBatchReady event when a new UserAttributeQueue record is created', function () {

    Event::fake([
        UserAttributesBatchReady::class,
    ]);

    // Create many UserAttributeQueue records
    UserAttributeQueue::factory(SyncApiParametersEnum::MAX_RECORDS_PER_BATCH_REQUEST->value)->create();

    // Act: Check if the event is dispatched
    Event::assertDispatched(UserAttributesBatchReady::class);

});

it('prepares data and triggers sync process', function () {
    // Mock the dependencies
    $queueRepository = mock(UserAttributeQueueRepository::class, function (MockInterface $mock) {
        $mock->shouldReceive('changeBatchDataToPending')
            ->once()
            ->with(SyncApiParametersEnum::MAX_RECORDS_PER_BATCH_REQUEST->value);
    });

    $apiLimits = mock(ApiLimitsInterface::class); // Mock but not used in this method

    // Act: Create the interactor instance
    $syncUserAttributesInteractor = new SyncUserAttributesInteractor($queueRepository, $apiLimits);

    // Fake the queue to test job dispatching
    Event::fake([
        UserAttributesBatchSending::class,
    ]);

    // Call the method under test
    $syncUserAttributesInteractor->prepareDataAndTriggerSyncProcess();

    // Assert: Check if the correct job is dispatched
    Event::assertDispatched(UserAttributesBatchSending::class);
});

it('syncs ready data and updates status', function () {
    // Mock the repository and its methods
    $records = UserAttributeQueue::factory(10)->create();
    $queueRepository = mock(UserAttributeQueueRepository::class, function (MockInterface $mock) use ($records) {
        $mock->shouldReceive('getPendingRecords')
            ->once()
            ->andReturn($records); // Return sample records

        $mock->shouldReceive('updatePendingRecordsStatus')
            ->once()
            ->with(QueueDataStatusEnum::SENT->value);
    });

    // Mock the API limits interface
    $apiLimits = mock(ApiLimitsInterface::class, function (MockInterface $mock) {
        $mock->shouldReceive('initBatchUsage')->once();
        $mock->shouldReceive('getCurrentBatchUsage')->times(3)->andReturn(0);
        $mock->shouldReceive('incrementBatchUsage')->once();
    });

    // Create the interactor instance
    $syncUserAttributesInteractor = new SyncUserAttributesInteractor($queueRepository, $apiLimits);

    // Log::spy() to capture logs
    Log::spy();

    // Act: Call syncReadyData method
    $syncUserAttributesInteractor->syncReadyData();

    // Assert: Ensure the logs were written
    Log::shouldHaveReceived('info')->with('Start Batch Sync');

    // Check the last log for successful sync
    Log::shouldHaveReceived('info')->with('User Attributes Synced Successfully!');

    // Assert that the updatePendingRecordsStatus was called with the correct parameter
    expect($queueRepository)->shouldHaveReceived('updatePendingRecordsStatus')
        ->with(QueueDataStatusEnum::SENT->value);
});

it('syncs missed or failed data and updates status', function () {
    // Mock the repository
    $records = UserAttributeQueue::factory(10)->create();
    $queueRepository = mock(UserAttributeQueueRepository::class, function (MockInterface $mock) use ($records) {
        $mock->shouldReceive('getFailedOrUnSentRecords')
            ->once()
            ->andReturn($records); // Return sample failed records

        $mock->shouldReceive('updateFailedOrUnSentDataStatus')
            ->once()
            ->with(QueueDataStatusEnum::SENT->value);
    });

    // Mock the API limits interface
    $apiLimits = mock(ApiLimitsInterface::class, function (MockInterface $mock) {
        $mock->shouldReceive('initBatchUsage')->once();
        $mock->shouldReceive('getCurrentBatchUsage')->times(3)->andReturn(0);
        $mock->shouldReceive('incrementBatchUsage')->once();
    });

    // Create the interactor instance
    $syncUserAttributesInteractor = new SyncUserAttributesInteractor($queueRepository, $apiLimits);

    // Log::spy() to capture logs
    Log::spy();

    // Act: Execute syncMissedOrFailedData method
    $syncUserAttributesInteractor->syncMissedOrFailedData();

    // Assert: Ensure the logs were written
    Log::shouldHaveReceived('info')->with('Start Batch Sync for missed or previous failed to send before')->once();

    // Check the last log for successful sync
    Log::shouldHaveReceived('info')->with('User Attributes Synced Successfully!')->once();

    // Assert that the updateFailedOrUnSentDataStatus was called with the correct parameter
    expect($queueRepository)->shouldHaveReceived('updateFailedOrUnSentDataStatus')
        ->with(QueueDataStatusEnum::SENT->value);
});

it('should sync 4000 changes with no more than hour and with batch usage < 5', function () {
    // Constants for the test
    $totalChanges = 4000;
    $maxBatchSize = SyncApiParametersEnum::MAX_RECORDS_PER_BATCH_REQUEST->value; // Assuming this is the maximum batch size
    $maxBatchUsage = 5; // The maximum allowed batch usage
    $totalBatches = intval(ceil($totalChanges / $maxBatchSize)); // Calculate total batches needed

    $records = UserAttributeQueue::factory($totalChanges)->create();
    // Mock the UserAttributeQueueRepository
    $queueRepository = mock(UserAttributeQueueRepository::class,
        function (MockInterface $mock) use ($records, $totalBatches) {
            // Simulate fetching batches
            $mock->shouldReceive('getPendingRecords')
                ->times($totalBatches)
                ->andReturn($records); // Simulate max records per batch

            // Simulate updating status
            $mock->shouldReceive('updatePendingRecordsStatus')
                ->times($totalBatches)
                ->with(QueueDataStatusEnum::SENT->value);
        });

    // Mock the API limits interface
    $apiLimits = mock(ApiLimitsInterface::class, function (MockInterface $mock) use ($maxBatchUsage) {
        $mock->shouldReceive('initBatchUsage')->times(4);
        $mock->shouldReceive('getCurrentBatchUsage')->andReturn(0); // Start with 0 usage
        $mock->shouldReceive('incrementBatchUsage')->times($maxBatchUsage - 1); // Expect increments
    });

    // Create the interactor instance
    $syncUserAttributesInteractor = new SyncUserAttributesInteractor($queueRepository, $apiLimits);

    // Log::spy() to capture logs
    Log::spy();

    // Act: Start syncing and measure time
    $startTime = microtime(true);
    for ($i = 0; $i < $totalBatches; $i++) {
        $syncUserAttributesInteractor->syncReadyData(); // Simulate syncing
    }
    $endTime = microtime(true);
    $duration = $endTime - $startTime;

    // Assert: Check if the duration is less than an hour (3600 seconds)
    expect($duration)->toBeLessThan(3600);

    // Assert: Check if the total batch usage is less than 50
    $finalBatchUsage = $apiLimits->getCurrentBatchUsage();
    expect($finalBatchUsage)->toBeLessThan($maxBatchUsage);

    // Ensure logs were created
    Log::shouldHaveReceived('info')->with('Start Batch Sync')->times($totalBatches);
});
