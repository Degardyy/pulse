<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * RBAC (ADR-007): permissions are declared in module code and synced here;
 * roles are data. A role grant (core_role_user) carries an optional scope —
 * both scope columns null means the grant is global, a division scope covers
 * that division and its departments, a department scope covers only itself.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('core_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 96)->unique();
            $table->string('name');
            $table->string('module', 32);
            $table->timestamps();
        });

        Schema::create('core_roles', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_super')->default(false);
            $table->timestamps();
        });

        Schema::create('core_permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained('core_permissions')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('core_roles')->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('core_role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('core_roles')->cascadeOnDelete();
            $table->foreignId('division_id')->nullable()->constrained('core_divisions')->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('core_departments')->restrictOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core_role_user');
        Schema::dropIfExists('core_permission_role');
        Schema::dropIfExists('core_roles');
        Schema::dropIfExists('core_permissions');
    }
};
