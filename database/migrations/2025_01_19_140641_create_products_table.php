<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->text('long_description')->nullable();
            $table->string('image1');
            $table->string('image2')->nullable();
            $table->string('image3')->nullable();
            $table->string('image4')->nullable();
            $table->string('image5')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('stock')->default(0);
            $table->decimal('price', 10, 2);
            $table->decimal('weight', 10, 2);
            $table->foreignId('category_id')->constrained()->nullOnDelete();
            $table->foreignId('color_id')->constrained()->nullOnDelete();
            $table->string('size')->nullable();
            $table->string('slug')->unique();
            $table->string('seo_keywords')->nullable();
            $table->unsignedBigInteger('product_group_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
