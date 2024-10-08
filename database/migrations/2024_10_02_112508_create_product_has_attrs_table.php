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
        Schema::create('product_has_attrs', function (Blueprint $table) {
            $table->unique(["product_id", "attr"]);
            $table->uuid("id")->primary();
            $table->foreignUuid("product_id")->constrained("products")->onDelete("cascade");
            $table->string("attr");
            $table->text("desc");
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_has_attrs');
    }
};
