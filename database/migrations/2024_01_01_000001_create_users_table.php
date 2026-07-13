<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 30)->nullable()->unique();
            $table->string('username', 50)->unique();
            $table->string('password_hash', 255);
            $table->string('nama_lengkap', 100);
            $table->string('email', 100)->nullable()->unique();
            $table->string('no_hp', 20)->nullable();
            $table->string('avatar', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->unsignedTinyInteger('failed_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->string('reset_token', 100)->nullable();
            $table->timestamp('reset_token_expires_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
