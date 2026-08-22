<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configuration-driven approval workflow (ADR-009). Definitions are templates;
 * when an instance starts, each template step is resolved into an instance
 * step with a concrete approver (position or role) frozen at that moment —
 * eligibility stays queryable and later org changes don't corrupt in-flight
 * approvals.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('core_workflow_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('core_workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('definition_id')->constrained('core_workflow_definitions')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order');
            $table->string('name');
            $table->string('approver_type', 32); // position | department_head | division_head | role
            $table->string('approver_value', 64)->nullable(); // position code / role code
            $table->timestamps();
        });

        Schema::create('core_workflow_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('definition_id')->constrained('core_workflow_definitions')->restrictOnDelete();
            $table->morphs('subject');
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->string('status', 16)->default('pending'); // pending | approved | rejected
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('core_workflow_instance_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instance_id')->constrained('core_workflow_instances')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order');
            $table->string('name');
            $table->foreignId('position_id')->nullable()->constrained('core_positions')->restrictOnDelete();
            $table->foreignId('role_id')->nullable()->constrained('core_roles')->restrictOnDelete();
            $table->string('status', 16)->default('pending'); // pending | approved | rejected
            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acted_at')->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamps();

            $table->index(['status', 'position_id']);
            $table->index(['status', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core_workflow_instance_steps');
        Schema::dropIfExists('core_workflow_instances');
        Schema::dropIfExists('core_workflow_steps');
        Schema::dropIfExists('core_workflow_definitions');
    }
};
