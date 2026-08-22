<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Documents with membership-scoped visibility: readable by one department,
 * one division (including its departments), or the whole organization.
 * Files live on the private disk — download only through the authorized route.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('core_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('description', 1000)->nullable();
            $table->string('category', 50)->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type', 128);
            $table->unsignedBigInteger('size');
            $table->string('visibility', 16); // paljaya | division | department
            $table->foreignId('division_id')->nullable()->constrained('core_divisions')->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('core_departments')->restrictOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['visibility', 'division_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core_documents');
    }
};
