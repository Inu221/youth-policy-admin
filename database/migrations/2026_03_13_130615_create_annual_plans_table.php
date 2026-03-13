<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annual_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id');
            $table->integer('year');
            $table->string('title');
            $table->string('status', 20)->default('draft');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_comment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['department_id', 'year']);
            $table->index('department_id');
            $table->index('year');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annual_plans');
    }
};