<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trust_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artisan_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('trust_score', 5, 4);
            $table->decimal('rating_component', 5, 4)->nullable();
            $table->decimal('completion_component', 5, 4)->nullable();
            $table->decimal('penalty', 5, 4)->nullable();
            $table->integer('review_count')->default(0);
            $table->integer('order_count')->default(0);
            $table->timestamps();

            $table->index(['trust_score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trust_cache');
    }
};
