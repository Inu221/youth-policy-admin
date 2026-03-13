<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actual_event_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actual_event_id');
            $table->unsignedBigInteger('participant_id');
            $table->unsignedBigInteger('added_by');
            $table->timestamp('created_at')->nullable();

            $table->unique(['actual_event_id', 'participant_id']);
            $table->index('actual_event_id');
            $table->index('participant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actual_event_participants');
    }
};