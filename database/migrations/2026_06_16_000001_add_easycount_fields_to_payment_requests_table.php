<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->string('easycount_doc_number')->nullable()->after('transaction_id');
            $table->string('easycount_doc_uuid')->nullable()->after('easycount_doc_number');
        });
    }

    public function down(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->dropColumn(['easycount_doc_number', 'easycount_doc_uuid']);
        });
    }
};
