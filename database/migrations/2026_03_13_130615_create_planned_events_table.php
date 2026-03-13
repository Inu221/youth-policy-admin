<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planned_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('annual_plan_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('planned_start_at');
            $table->dateTime('planned_end_at')->nullable();
            $table->string('location_name')->nullable();
            $table->string('location_url', 1000)->nullable();
            $table->unsignedBigInteger('responsible_user_id');
            $table->integer('planned_participants_count')->nullable();
            $table->string('status', 20)->default('planned');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('annual_plan_id');
            $table->index('responsible_user_id');
            $table->index('planned_start_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planned_events');
    }
};