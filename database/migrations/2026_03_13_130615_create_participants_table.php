<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->date('birth_date')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->integer('attendance_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('full_name');
            $table->index('attendance_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};