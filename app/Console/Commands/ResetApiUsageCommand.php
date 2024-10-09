<?php

namespace App\Console\Commands;

use App\Features\SyncUserAttributes\Application\Contracts\ApiLimitsInterface;
use Illuminate\Console\Command;

class ResetApiUsageCommand extends Command
{
    public function __construct(
        private readonly ApiLimitsInterface $apiLimits
    ) {
        parent::__construct();

    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apiUsage:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'You can reset Api Usage';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->apiLimits->resetBatchUsage();
        $currentApiUsage = $this->apiLimits->getCurrentBatchUsage();
        // Provide feedback
        $this->info("Api current usage {$currentApiUsage}");

        return 0;
    }
}
