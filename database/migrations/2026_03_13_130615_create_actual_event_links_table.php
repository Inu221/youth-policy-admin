<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actual_event_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actual_event_id');
            $table->string('link_type', 30)->default('social_post');
            $table->string('url', 1000);
            $table->boolean('is_primary')->default(false);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at')->nullable();

            $table->index('actual_event_id');
            $table->index('link_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actual_event_links');
    }
};