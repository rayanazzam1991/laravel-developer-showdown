<?php

use App\Features\SyncUserAttributes\Domain\Enum\QueueDataStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_attributes_queue', function (Blueprint $table) {
            $table->id();
            $table->json('payload');
            $table->tinyInteger('status')->default(QueueDataStatusEnum::UN_SENT->value);
            $table->integer('retry_count')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_attributes_queue');
    }
};
