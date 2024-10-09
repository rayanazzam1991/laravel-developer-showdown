# Description 

A Laravel-based solution to manage API requests efficiently within rate limits.
The system ensures user attributes are updated in sync with a third-party API while adhering to request limits:
50 batch requests per hour (1,000 records per batch) and 3,600 individual requests per hour.

The solution processes approximately 40,000 updates per hour, making optimal use of batch processing to stay within API limits.

## User Requirements

- Detect when a user’s data has changed and trigger an update action to sync with the third-party provider.
- Group user updates and send them in batches to the third-party API to minimize the number of API calls and stay within the rate limits.
- Queue and store user update data when API rate limits are reached, ensuring the updates are sent later once the limit resets.

### Nice to have 
- Error Handling: How errors from the third-party API (e.g., failed batch requests) should be handled, including retry mechanisms.
- Logging: Requirements for logging API calls and responses for monitoring and troubleshooting.


# Implementation

1. User Data Change Detection & Event Trigger:
      - Action/Events: Every time a user updates their data, an event is triggered to check if any relevant fields have changed.
      - Queue Entry: If changes are detected, the updated data is stored in a database queue table, marking it for processing.
2. Queue Design:
      - Database Choice: MySQL is a good choice for this type of system, especially since you’re not dealing with real-time messaging.
        - Table Structure:
          - AutoIncrementID: Unique identifier for each record.
          - BatchID: Groups of 1,000 records to facilitate batch processing. This increments every 1,000 entries.
          - Payload: Contains the user’s updated data, consider using JSON or a serialized format for the payload to store multiple attributes easily.
          - Status: A status flag (0 for unsent/failed, 1 for sent successfully).
          - Timestamps: Add created_at and updated_at fields to track when the record was queued and when it was last processed.
          - Retry Count: Consider adding a retry_count field to avoid retrying indefinitely for records that keep failing.
3. Batch Processing:
      - Batch Size: Group user data into batches of 1,000 records, keeping track of the BatchID.
      - Triggering the Batch: When a new record is added to the queue, check if the batch size has reached 1,000. If so, send the batch to the third-party API.
      - Handling Responses:
        - If the API response is successful (OK), mark the batch’s records with status = 1.
        - If the response fails, do not update the status and log the error for retry later.
4. Cron Job for Failures:
      - Cron Frequency: we will be firing the cron job every hour or at specific times of low traffic.
      - Five-Minute Early Execution: Running the cron job five minutes before the hour ends to handle remaining records.
      - Retry Logic: The cron job will:
        - Check for any records with status = 0 (failed) and attempt to resend them.
        - Send records in batches until the API limit is reached.
        - Keep unprocessed records in the queue if the limit is exceeded and process them in the next hour.
5. Rate Limit Awareness:
      - API Rate Limits: Since we are handling 40,000 calls per hour (including retries),
        we need to track the API rate usage carefully, so I will store information about limits in an In-memory Data Store like Redis,
      - Store Rate Limits data:
        - Total API calls made in the current hour (for both batch and individual requests).
        - Remaining API calls allowed.

### Extra ###
Monitoring & Alerts:
   - Logging: Implement robust logging for both successful and failed API calls. This will help with debugging and performance monitoring.
   - Alert System: Set up alerts to notify you if there are persistent failures in sending data or if records remain in the queue for too long without being processed.

## Important: 

**Handling Large Volumes (for later)**
- Dynamic Cron Triggering: You could further optimize the cron job by dynamically adjusting its frequency based on the current load. For example, if the queue grows faster than expected, the cron job could run more frequently within the hour. 
 However,rather than relying on “guessing” the low-traffic times, you can use historical data and traffic analysis to fine-tune the optimal cron run times.
- Scaling Consideration: In high-traffic systems, you may need to consider partitioning the queue table to improve performance or archiving old data after successful API calls to keep the table efficient. 
- Queue Backup: Consider implementing queue backup strategies (e.g., offloading old records to another table or a different storage system) in case your MySQL queue grows too large due to temporary API failures.
- Queue System : For now, using a database-backed queue is a perfectly fine solution for handling 40,000 calls per hour. It’s simple, cost-effective, and easy to implement with the tools you already have.
However, as traffic increases or the system becomes more complex I advise transitioning to a dedicated message queue like RabbitMQ, Redis, or AWS SQS will give you more scalability, flexibility, and robustness.




