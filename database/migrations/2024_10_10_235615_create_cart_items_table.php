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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->foreignUuid("cart_id")->constrained("carts")->onDelete("cascade");
            $table->foreignUuid("product_id")->constrained("carts")->onDelete("cascade");
            $table->float('price');
            $table->integer('quantity');
            $table->float('total');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
