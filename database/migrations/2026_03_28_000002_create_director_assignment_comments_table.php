<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('director_assignment_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('director_assignment_id');
            $table->text('comment');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->softDeletes();

            $table->index('director_assignment_id');
            $table->index('user_id');

            $table->foreign('director_assignment_id')
                ->references('id')
                ->on('director_assignments')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('director_assignment_comments');
    }
};
