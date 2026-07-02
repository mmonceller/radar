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
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Distinguish store medium types
            $table->enum('type', ['online', 'physical'])->default('physical');
            $table->string('store_name');
            
            // Flexible fields depending on the 'type'
            $table->string('link')->nullable(); // Used for online stores
            $table->decimal('latitude', 10, 8)->nullable();  // Used for physical map pins
            $table->decimal('longitude', 11, 8)->nullable(); // Used for physical map pins
            
            // Item verification details
            $table->decimal('price', 8, 2); 
            $table->string('image_path'); // Required "proof" picture
            
            // The "Still Updated" tracker field
            $table->timestamp('last_verified_at')->useCurrent(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
