<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Organization structure: Directorate → Division → Department.
 * Internal Audit reports directly to the President Director without departments,
 * so divisions carry a type ('division' | 'unit') instead of a separate table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('core_directorates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 16)->unique();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('core_divisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('directorate_id')->constrained('core_directorates')->restrictOnDelete();
            $table->string('code', 16)->unique();
            $table->string('name');
            $table->string('type', 16)->default('division');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('core_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained('core_divisions')->restrictOnDelete();
            $table->string('code', 16)->unique();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core_departments');
        Schema::dropIfExists('core_divisions');
        Schema::dropIfExists('core_directorates');
    }
};
