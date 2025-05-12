<?php

use App\Models\AgentAvailability;
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
        Schema::create('work_load_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_id')->constrained('queues')->onDelete('cascade');
            $table->integer('phone_calls_per_hour')->default(0);
            $table->date('date');
            $table->time('start_hour')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_load_predictions');
    }
};
