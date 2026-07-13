<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('username', 50)->nullable();
            $table->string('modul', 50)->nullable();
            $table->string('aksi', 100);
            $table->string('tabel', 100)->nullable();
            $table->string('record_id', 50)->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index(['modul', 'aksi']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};
