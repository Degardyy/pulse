<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Org-wide publication can now go through approval (ADR-009): a document is
 * published (visible per its scope), pending approval (uploader/approvers
 * only), or rejected (uploader only).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('core_documents', function (Blueprint $table) {
            $table->string('status', 24)->default('published')->after('visibility');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('core_documents', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};
