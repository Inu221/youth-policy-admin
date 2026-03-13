<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('id');
            $table->string('full_name')->nullable()->after('department_id');
            $table->string('username')->nullable()->unique()->after('full_name');
            $table->string('role', 30)->default('department_head')->after('password');
            $table->string('phone', 50)->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('phone');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->softDeletes();

            $table->index('department_id');
            $table->index('role');
            $table->index(['department_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['department_id']);
            $table->dropIndex(['role']);
            $table->dropIndex(['department_id', 'role']);

            $table->dropSoftDeletes();
            $table->dropColumn([
                'department_id',
                'full_name',
                'username',
                'role',
                'phone',
                'is_active',
                'last_login_at',
            ]);
        });
    }
};