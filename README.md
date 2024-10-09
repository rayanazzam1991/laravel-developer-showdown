# Description

A Laravel-based solution to manage API requests efficiently within rate limits. The system ensures user attributes are updated in sync with a third-party API while adhering to request limits: 50 batch requests per hour (1,000 records per batch) and 3,600 individual requests per hour.

The solution processes approximately 40,000 updates per hour, making optimal use of batch processing to stay within API limits.

## User Requirements

- Detect when a user’s data has changed and trigger an update action to sync with the third-party provider.
- Group user updates and send them in batches to the third-party API to minimize the number of API calls and stay within the rate limits.
- Queue and store user update data when API rate limits are reached, ensuring the updates are sent later once the limit resets.

### Nice to Have

- **Error Handling**: Define how errors from the third-party API (e.g., failed batch requests) should be managed, including retry mechanisms.
- **Logging**: Establish requirements for logging API calls and responses for monitoring and troubleshooting.

# Implementation
### I highly recommend you to watch this video first [Link](https://drive.google.com/file/d/1DuYwIf_WO-ipaNKRWNGXUnFn4Ox3RfeH/view?usp=sharing)
1. **User Data Change Detection & Event Trigger**:
    - **Action/Events**: Every time a user updates their data, an event is triggered to check if any relevant fields have changed.
    - **Queue Entry**: If changes are detected, the updated data is stored in a database queue table, marking it for processing.

2. **Queue Design**:
    - **Database Choice**: MySQL is suitable for this type of system, especially since real-time messaging is not involved.
        - **Table Structure**:
            - **AutoIncrementID**: Unique identifier for each record.
            - **Payload**: Contains the user’s updated data, ideally in JSON or a serialized format for easy attribute storage.
            - **Status**: A status flag (0 for unsent, 1 for pending, 2 for sent successfully, 3 for failed).
            - **Timestamps**: Include `created_at` and `updated_at` fields to track when the record was queued and last processed.
            - **Retry Count**: A `retry_count` field to avoid indefinite retries for failing records.

3. **Batch Processing**:
    - **Batch Size**: Group user data into batches of 1,000 records.
    - **Triggering the Batch**: When a new record is added to the queue, check if the batch size has reached 1,000. If so, send the batch to the third-party API.
    - **Handling Responses**:
        - If the API response is successful (`OK`), mark the batch’s records with status = SENT.
        - If the response fails, mark the batch’s records with status = Failed.

4. **Scheduled Task for Failures**:
    - **Scheduler**: Utilize **Laravel's task scheduler**, which runs every minute inside a Docker container managed by **Supervisor**.
    - **Retry Logic**:
        - The scheduler checks every minute for any records with status = 3 (failed) and attempts to resend them.
        - An hourly command is also triggered to process unsent or failed records (status = 0 or 3).

5. **Rate Limit Awareness**:
    - **API Rate Limits**: Since the system handles 40,000 calls per hour (including retries), tracking API rate usage is crucial.
    - **In-Memory Data Store (Redis)**:
        - Store the total API calls made in the current hour, including both batch and individual requests.
        - Track remaining API calls allowed to ensure the system does not exceed the rate limit.


## Important 
#### for Future to be worked on

### Monitoring & Alerts
- **Logging**: Implement robust logging for both successful and failed API calls for debugging and performance monitoring.
- **Alert System**: Set up alerts to notify of persistent failures in data sending or if records remain in the queue for too long without processing.


### Handling Large Volumes

- **Dynamic Scheduler**: Consider optimizing the scheduler by dynamically adjusting its frequency based on current load. Use historical data and traffic analysis to determine optimal run times.
- **Scaling Consideration**: For high-traffic systems, consider partitioning the queue table for improved performance or archiving old data after successful API calls.
- **Queue Backup**: Implement queue backup strategies (e.g., offloading old records to another table) in case your MySQL queue grows too large due to temporary API failures.
- **Queue System**: A database-backed queue is currently effective for handling 40,000 calls per hour. However, as traffic increases or the system complexity grows, transitioning to a dedicated message queue (e.g., RabbitMQ, Redis, or AWS SQS) will provide more scalability and robustness.

# Building

1. **Clone the Repo**:
    ```bash
    git clone git@github.com:rayanazzam1991/laravel-developer-showdown.git
    ```
2. **Run Docker** on your machine or laptop.
3. **Install Dependencies**:  
   If you have Composer installed, run:
    ```bash
    composer install
    ```
   Otherwise, run:
    ```bash
    docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
    ```
4. **Run Sail**:
    ```bash
    ./vendor/sail/up
    ```
5. **Run Migrations**:
    ```bash
    sail artisan migrate:fresh --seed
    ```
6. **Clear the Log** to prevent any unwanted data, and you are good to go!

# QA and Testing

Please watch this video [Link](https://drive.google.com/file/d/1CM66dIlcIzSb208W4LKOBaEeXmWgEVqN/view?usp=sharing)
or follow these steps:

1. **Sync Batch**:
    ```bash
    sail artisan sync:batch
    ```
2. **Choose the Number of Times** you want to trigger changes to the 20 users in the database. For example:
    ```bash
    sail artisan sync:batch 100
    ```
   Then you will have 100 * 20 records in the queue, triggering the API calls 100 * 20 /1000 = 2 Calls.
