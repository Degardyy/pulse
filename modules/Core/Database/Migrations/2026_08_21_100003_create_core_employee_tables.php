<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Positions are structural seats attached to exactly one org unit (directorate,
 * division, or department). Employees hold seats through assignments — a
 * separate table because one person can hold several seats at once (definitive
 * plus acting/"Plt"), and a seat with no active assignment is vacant.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('core_positions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->string('level', 32);
            $table->foreignId('directorate_id')->nullable()->constrained('core_directorates')->restrictOnDelete();
            $table->foreignId('division_id')->nullable()->constrained('core_divisions')->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('core_departments')->restrictOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('core_employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('employee_number', 32)->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('core_position_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->constrained('core_positions')->restrictOnDelete();
            $table->foreignId('employee_id')->constrained('core_employees')->restrictOnDelete();
            $table->boolean('is_acting')->default(false);
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->timestamps();

            $table->index(['position_id', 'ended_at']);
            $table->index(['employee_id', 'ended_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core_position_assignments');
        Schema::dropIfExists('core_employees');
        Schema::dropIfExists('core_positions');
    }
};
