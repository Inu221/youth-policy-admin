<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actual_event_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actual_event_id');
            $table->string('stored_name');
            $table->string('original_name');
            $table->string('file_path', 1000);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamp('created_at')->nullable();

            $table->index('actual_event_id');
            $table->index('uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actual_event_files');
    }
};