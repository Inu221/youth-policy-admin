<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actual_event_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actual_event_id')->unique();
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('reviewer_id')->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('reviewer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actual_event_verifications');
    }
};