<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->timestamp('awarded_at')->nullable();
            $table->string('verification_image_path')->nullable();
            $table->decimal('verified_price', 10, 2)->nullable();
            $table->timestamp('purchase_verified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'awarded_at',
                'verification_image_path',
                'verified_price',
                'purchase_verified_at',
            ]);
        });
    }
};
