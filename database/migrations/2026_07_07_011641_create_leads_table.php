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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('description'); // Where to buy/find the item
            $table->string('source_link')->nullable(); // Optional map or store link
            $table->integer('upvotes_count')->default(0); // Cached for ultra-fast sorting
            $table->integer('downvotes_count')->default(0);
            $table->timestamp('last_verified_at')->nullable(); // Tracks when it was last voted on
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
