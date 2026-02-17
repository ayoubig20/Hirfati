<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artisans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();
            $table->string('service_category', 100);
            $table->string('specialty')->nullable();
            $table->string('location');
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->decimal('avg_rating', 3, 2)->default(0.00);
            $table->integer('jobs_completed')->default(0);
            $table->decimal('trust_score', 5, 4)->default(0.5000);
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamps();

            $table->index('service_category');
            $table->index('location');
            $table->index(['trust_score']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artisans');
    }
};
