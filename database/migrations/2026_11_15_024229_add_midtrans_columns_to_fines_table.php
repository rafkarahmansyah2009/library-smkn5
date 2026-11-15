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
        Schema::table('fines', function (Blueprint $table) {
            if (! Schema::hasColumn('fines', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('status');
            }
            if (! Schema::hasColumn('fines', 'midtrans_order_id')) {
                $table->string('midtrans_order_id')->nullable()->unique()->after('payment_method');
            }
            if (! Schema::hasColumn('fines', 'snap_token')) {
                $table->string('snap_token')->nullable()->after('midtrans_order_id');
            }
            if (! Schema::hasColumn('fines', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fines', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['payment_method', 'midtrans_order_id', 'snap_token', 'paid_at'] as $col) {
                if (Schema::hasColumn('fines', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
