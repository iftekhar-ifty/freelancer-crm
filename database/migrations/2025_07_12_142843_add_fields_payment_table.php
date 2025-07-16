<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('status', ['partial', 'full', 'overpayment'])->default('full');
            $table->decimal('applied_amount', 10, 2)->comment('Amount applied to milestone/project');
            $table->decimal('excess_amount', 10, 2)->default(0)->comment('Overpayment amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('applied_amount');
            $table->dropColumn('excess_amount');
        });
    }
};
