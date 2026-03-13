<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actual_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('planned_event_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('actual_start_at');
            $table->dateTime('actual_end_at')->nullable();
            $table->string('location_name')->nullable();
            $table->string('location_url', 1000)->nullable();
            $table->unsignedBigInteger('responsible_user_id');
            $table->integer('planned_participants_snapshot')->nullable();
            $table->integer('actual_participants_count')->default(0);
            $table->string('status', 20)->default('planned');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('department_id');
            $table->index('planned_event_id');
            $table->index('responsible_user_id');
            $table->index('actual_start_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actual_events');
    }
};