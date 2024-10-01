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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained("orders")->onDelete("cascade");
            $table->foreignUuid('user_id')->constrained("users")->onDelete("cascade");
            $table->string('payment_method');
            $table->enum('payment_status', ['pending', 'completed', 'failed']);
            $table->string('transaction_id');
            $table->decimal('amount', 10, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
