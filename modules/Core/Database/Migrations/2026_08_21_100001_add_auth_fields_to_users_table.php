<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hybrid authentication (ADR-006): accounts are local today, but the schema is
 * SSO-ready — auth_provider/provider_id identify the external identity source,
 * and password is nullable because SSO accounts never store one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
            $table->string('auth_provider', 32)->default('local')->after('remember_token');
            $table->string('provider_id')->nullable()->after('auth_provider');
            $table->boolean('is_active')->default(true)->after('provider_id');
            $table->timestamp('last_login_at')->nullable()->after('is_active');

            $table->unique(['auth_provider', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['auth_provider', 'provider_id']);
            $table->dropColumn(['auth_provider', 'provider_id', 'is_active', 'last_login_at']);
            $table->string('password')->nullable(false)->change();
        });
    }
};
