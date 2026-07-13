<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique();
            $table->string('nama', 100);
            $table->string('deskripsi', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // User ↔ Role (pivot)
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
            $table->unique(['user_id', 'role_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });

        // Menus
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('kode', 100)->unique();
            $table->string('label', 100);
            $table->string('icon', 100)->nullable();
            $table->string('url', 255)->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('parent_id')->references('id')->on('menus')->nullOnDelete();
        });

        // Role ↔ Menu access (permission: 1=view, 3=create/edit, 7=full+delete)
        Schema::create('role_menu_access', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('menu_id');
            $table->unsignedTinyInteger('permission')->default(1); // bitmask
            $table->unique(['role_id', 'menu_id']);
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->foreign('menu_id')->references('id')->on('menus')->cascadeOnDelete();
        });

        // User-level menu override (opsional, bypass role)
        Schema::create('user_menu_override', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('menu_id');
            $table->unsignedTinyInteger('permission')->default(0);
            $table->unique(['user_id', 'menu_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('menu_id')->references('id')->on('menus')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_menu_override');
        Schema::dropIfExists('role_menu_access');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');
    }
};
